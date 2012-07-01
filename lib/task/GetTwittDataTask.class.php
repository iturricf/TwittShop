<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetTwittDataTask
 *
 * @author mr29a
 */
class GetTwittDataTask extends sfBaseTask {

    public function configure() {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'front'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
//            new sfCommandOption('first_interval', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
//            new sfCommandOption('second_interval', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
        ));

        $this->namespace = 'twitterStore';
        $this->name = 'getData';
        $this->briefDescription = 'get All necesary dato from twitter';
    }

    public function execute($arguments = array(), $options = array()) {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase('doctrine')->getConnection();
        
        $last_twitt = Doctrine_Query::create()->from('TwittBase tb')->orderBy('tb.tw_timestamp desc')->limit(1)->fetchOne();
        
        $json_url = "http://search.twitter.com/search.json?q=@inmzombie&include_entities=true&result_type=mixed";
        
        //$json_url = "http://search.twitter.com/search.json?q=@inmzombie&include_entities=true&result_type=mixed&since=".$last_twitt->getTwId();
        
        $json_data = file_get_contents($json_url);
        $data_twitt = json_decode($json_data, true);
        
        $this->log("DATA: =>=>=>" . $json_data);
        
        foreach ($data_twitt['results'] as $key => $data) {
            $is_queiro = strstr(strtolower($data['text']), 'quiero');
            $is_mandame = strstr(strtolower($data['text']), 'mandame');
            if ($is_mandame || $is_queiro) {
                $nickname = $data['from_user'];
                $idUser = $data['from_user_id'];
                $username = $data['from_user_name'];
                $id = $data['id'];
                $content = $data['text'];
                //echo var_dump($data["created_at"]) . '<br>';
                $timestamp = strtotime($data['created_at']);
                foreach ($data['entities'] as $j => $entitie) {
                    if ($j == 'hashtags') {
                        foreach ($entitie as $hashtag) {
                            echo $hashtag["text"];
                        };
                    }
                    
                    
                    
                    
                }
                
                $twittBase = new TwittBase();
                    $twittBase->setTwUsername($username);
                    $twittBase->setTwNickname($nickname);
                    $twittBase->setTwId($id);
                    $twittBase->setTwTimestamp($timestamp);
                    $twittBase->setTwUserId($idUser);
                    $twittBase->setContent($content);
                    $twittBase->save();
                    
                    $this->log("Fetched Twitt");
                    $this->log("--------------");
                    $this->log("Username : " . $twittBase->getTwUsername());
                    $this->log("Nickname : " . $twittBase->getTwNickname());
                    $this->log("Tw ID    : " . $twittBase->getTwId());
                    $this->log("Timestamp: " . $twittBase->getTwTimestamp());
                    $this->log("Tw User  : " . $twittBase->getTwUserId());
                    $this->log("Content  : " . $twittBase->getContent());
            }
        }
    }

}


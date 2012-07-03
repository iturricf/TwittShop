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
class RetrieveTwittsForStoreTask extends sfBaseTask {

    public function configure() {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'admin'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
        ));

        $this->namespace = 'twittShop';
        $this->name = 'retrieveTwittsForStore';
        $this->briefDescription = 'Retrieves mentions of the store from Twitter,'.
                                    ' filters users Orders and saves to the database';
    }

    public function execute($arguments = array(), $options = array()) {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase('doctrine')->getConnection();
        
        // The last twitt we have in database
        $lastTwitt = Doctrine_Query::create()->from('TwittBase tb')
                                    ->orderBy('tb.tw_timestamp desc')->limit(1)->fetchOne();
        
        $twQuery['q']                   = '{store_user} {magic_word}';
        $twQuery['since_id']            = ($lastTwitt) ? $lastTwitt->getTwId(): '';
        $twQuery['result_type']         = 'recent';
        $twQuery['include_entities']    = 1;

        $twQuery['q'] = strtr($twQuery['q'], array(
                        '{store_user}' => sfConfig::get('app_tw_api_store_user'),
                        '{magic_word}' => sfConfig::get('app_tw_api_store_magic_word'),
        ));

        $apiCall  = sfConfig::get('app_tw_api_search_server') . sfConfig::get('app_tw_api_search_service');

        $apiCall .= '?' .http_build_query($twQuery);

        $this->log("Quering Twitter API: " . $apiCall);
        
        $apiResponse = file_get_contents($apiCall);
        $retrievedData = json_decode($apiResponse, true);
        
        //$this->log("DATA: =>=>=>" . $apiResponse);
        $twCounter = 0;
        foreach ($retrievedData['results'] as $key => $value) {
            $nickname = $value['from_user'];
            $idUser = $value['from_user_id'];
            $username = $value['from_user_name'];
            $id = $value['id'];
            $content = $value['text'];
            $timestamp = strtotime($value['created_at']);

            $this->log("\nFetched Twitt");
            $this->log("--------------");
            $this->log("Username : " . $username);
            $this->log("Nickname : " . $nickname);
            $this->log("Tw ID    : " . $id);
            $this->log("Timestamp: " . $timestamp);
            $this->log("Tw User  : " . $idUser);
            $this->log("Content  : " . $content);
            $this->log('Hashtags : ');
            foreach ($value['entities']['hashtags'] as $j => $hashtag) {
                $this->log('--> ' .$hashtag["text"]);
            }
            
            $twittBase = new TwittBase();
            $twittBase->setTwUsername($username);
            $twittBase->setTwNickname($nickname);
            $twittBase->setTwId($id);
            $twittBase->setTwTimestamp($timestamp);
            $twittBase->setTwUserId($idUser);
            $twittBase->setContent($content);
            $twittBase->save();
            
            $twCounter++;
        }

        $this->log("\n\nProcess finished.\n-----------------");
        $this->log('Result: ' .$twCounter. ' twitts saved to database');
    }
}
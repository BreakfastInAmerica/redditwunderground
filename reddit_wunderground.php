#!/usr/bin/php
<?php
/*
* This is a modified script found at http://stackoverflow.com/questions/7437707/reddit-api-in-php-returns-bad-captcha-for-submitting-story
* Not sure what HttpRequest class they were using so I modified it to use PEAR HTTP_Request2
* So the author is Ramji @ stackoverflow with // comments.
* Wunderground addition, modifications to Ramji's code,  and */ /* comments by BreakfastInAmerica
*/
include('HTTP/Request2.php');

/*
* Config
*/

/* weather underground info */
$city="Knoxville";
$state="TN"; 
$apiKey="getyourkeyfromweatherunderground";

/* reddit username and password. 
User name cannot be a new user or 
you will need to add captcha support */
$user = "redditusername";
$passwd = "redditpassword";
$subreddit = "subredditname"; /* the subreddit in which you want to submit to */

/*###########################################################*/
/* You should not need to edit anything else unless you want */
/*###########################################################*/

/*
* Get the weather information
*/
$wuUrl = "http://api.wunderground.com/api/$apiKey/geolookup/conditions/q/$state/$city.json";
$json_string = file_get_contents($wuUrl); 
$parsed_json = json_decode($json_string); 

/*
* Build the string to submit to reddit
*/
$temp_f = $parsed_json->{'current_observation'}->{'temperature_string'}; 
$weather = $parsed_json->{'current_observation'}->{'weather'};
$wind = $parsed_json->{'current_observation'}->{'wind_string'};
$humidity = $parsed_json->{'current_observation'}->{'relative_humidity'};
$feelslike = $parsed_json->{'current_observation'}->{'feelslike_string'}; 
$datetime = date('F j, Y, g:i A');
$string = "$datetime: Current Temp: $temp_f, Current Condition: $weather, Wind: $wind, Humidity: $humidity, Feels Like: $feelslike";

/*
* Login to redit and get the token to post comment
*/
$url = "https://ssl.reddit.com/api/login/".$user; /* Use SSL, its what winners do */
$r = new HTTP_Request2($url, HTTP_Request2::METHOD_POST);
$r->setConfig(array( 'ssl_verify_peer'   => FALSE, 'ssl_verify_host'   => FALSE)); /* fix for SSL certs */
$r->addPostParameter(array('api_type' => 'json', 'user' => $user, 'passwd' => $passwd));
try {
    $send = $r->send();
    $userinfo = $send->getBody();
} catch (HTTP_Request2_Exception $ex) {
    echo $ex;
}

/*
* get session info
*/
$arr = json_decode($userinfo,true); 
$modhash = $arr['json']['data']['modhash'];
$reddit_session = $arr['json']['data']['cookie'];

/*
* Create your post request array
*/
$post = array('uh'=>$modhash,
               'kind'=>'self',
                'text'=> "The current weather information is: " . $string . ". Source: Weather Underground, Inc.",
                'sr'=> $subreddit,
                'title'=>$string, 
                'renderstyle'=> 'html'              
                );

/*
* Set URL for submit
*/
$url = "https://ssl.reddit.com/api/submit";

/* Fixed to work with HTTP/Request2 without modifications */
$headers = $r->getHeaders();
$cookie = "reddit_session" . '=' . $reddit_session;
$cookies = empty($headers['cookie'])? '': $headers['cookie'] . '; ';
$r->setHeader('cookie', $cookies . $cookie);

// Set URL to submit
$r->setUrl($url);
// Add vote information, found at http://wiki.github.com/talklittle/reddit-is-fun/api-all-functions
$r->addPostParameter($post);
// Send request blindly

/*
* Send the comment to reddit
*/
try {
    $userinfo = $r->send();
} catch (HTTP_Request2_Exception $ex) {
    echo $ex;   
} 

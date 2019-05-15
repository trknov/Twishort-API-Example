/*
 * This PHP example demonstrates a very simple use of the Twishort API
 * More about Twishort API: https://twishort.com/api
 */

// Twitter Auth tokens. Get yours at https://developer.twitter.com/apps
$twitter_auth = array(
  'consumer_key'    => 'consumer_key of your Twitter app',
  'consumer_secret' => 'consumer_secret of your Twitter app',
  'token'      => 'user_token of authenticated user',
  'secret'     => 'user_secret of authenticated user',
  // 'user_token'      => '', // before tmhOAuth v0.8.4
  // 'user_secret'     => '', // before tmhOAuth v0.8.4
);
$twishort_key = 'your Twishort API key'; // get your API key at http://twishort.com/api

$x_auth_service_provider = 'https://api.twitter.com/1.1/account/verify_credentials.json';
$twishort_post_url = 'https://api.twishort.com/1.1/post.json';
$twishort_update_ids_url = 'https://api.twishort.com/1.1/update_ids.json';
// End settings

$title = 'post title';
$text = 'post text';

require('tmhOAuth.php'); // we use tmhOAuth library in this example (https://github.com/themattharris/tmhOAuth)
$tmhOAuth = new tmhOAuth($twitter_auth);

// generate the verify crendentials header -- BUT DON'T SEND
// we prevent the request because we're not the ones sending the verify_credentials request, the delegator is

// $tmhOAuth->config['prevent_request'] = true; // before tmhOAuth v0.8.4
$tmhOAuth->config['block'] = true;
$tmhOAuth->request('GET', $x_auth_service_provider);
$tmhOAuth->config['block'] = false;
// $tmhOAuth->config['prevent_request'] = false; // before tmhOAuth v0.8.4

// create the headers for the echo
$tmhOAuth->headers = array(
  'X-Auth-Service-Provider'            => $x_auth_service_provider,
  'X-Verify-Credentials-Authorization' => $tmhOAuth->auth_header,
);

// prepare the request to the delegator (Twishort)
$params = array(
  'api_key' => $twishort_key,
  'text' => $text,
  'title' => $title, // optional  
);  

// make the request, no auth, custom headers
$code = $tmhOAuth->request('POST', $twishort_post_url, $params, false, true);

if($code != 200) { // error
  echo 'ERROR! ';
  print_r($tmhOAuth->response['error']);
  print_r($tmhOAuth->response['response']);
  exit();
}

// success
$post = json_decode($tmhOAuth->response['response'], 1);
print_r($post);
/* 
$post = Array
(
    [id] => cbbbc
    [url] => http://twishort.com/cbbbc
    [created_at] => Fri, 07 Dec 2012 14:27:28 +0000
    [text_to_tweet] => text to post… http://twishort.com/cbbbc
    [user] => Array
        (
            [id] => 835057694
            [id_str] => 835057694
            [screen_name] => test_user
        )
)
*/

// OPTIONAL BUT HIGHLY RECOMMENDED PART
// Here you posting text from $post['text_to_tweet'] to Twitter by yourself
// Twitter will return to you result set that will among others include fields 'id_str', 'in_reply_to_status_id_str' and 'in_reply_to_user_id_str'.
// Let's say you save response from Twitter to $tweet variable
// Now send these Twitter ids back to Twishort
$params = array(
  'api_key' => $twishort_key,  
  'id' => $post['id'],
  'tweet_id' => $tweet['id_str'],
  'reply_to_tweet_id' => $tweet['in_reply_to_status_id_str'], // optional
  'reply_to_user_id' => $tweet['in_reply_to_user_id_str'], // optional
  'media' => $tweet['entities']['media'], // optional
);  
// make the request, no auth, custom headers
$code = $tmhOAuth->request('POST', $twishort_update_ids_url, $params, false, true);

// Done, thank you for using Twishort API =)

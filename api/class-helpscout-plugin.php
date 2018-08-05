<?php
/*
 * Handles the response to Help Scout queries
 *
 */
class CUSTOM_HELPSCOUT_PLUGIN_HANDLER
{
    private $input = false;
    /**
     * Returns the requested HTTP header.
     *
     * @param string $header
     * @return bool|string
     */
    private function getHeader($header)
    {
        if (isset($_SERVER[$header])) {
            return $_SERVER[$header];
        }
        return false;
    }
    /**
     * Retrieve the JSON input
     *
     * @return bool|string
     */
    private function getJsonString()
    {
        if ($this->input === false) {
            $this->input = @file_get_contents('php://input');
        }
        return $this->input;
    }
    /**
     * Generate the signature based on the secret key, to compare in isSignatureValid
     *
     * @return bool|string
     */
    private function generateSignature()
    {
        $str = $this->getJsonString();
        if ($str) {
            return base64_encode(hash_hmac('sha1', $str, 'rSPqYHmN4KsGxmY4roNKAEttDz9ZaHBfv8Ln4t1v', true));
        }
        return false;
    }
    /**
     * Returns true if the current request is a valid webhook issued from Help Scout, false otherwise.
     *
     * @return boolean
     */
    private function isSignatureValid()
    {
        $signature = $this->generateSignature();
        if (!$signature || !$this->getHeader('HTTP_X_HELPSCOUT_SIGNATURE')) {
            return false;
        }
        return $signature == $this->getHeader('HTTP_X_HELPSCOUT_SIGNATURE');
    }
    /**
     * Create a response.
     *
     * @return array
     */
    public function getResponse()
    {
        $ret = array('html' => '');
        if (!$this->isSignatureValid()) {
            return array('html' => 'Invalid signature');
        }
        $data = json_decode($this->input, true);
        // do some stuff
        $ret['html'] = $this->fetchHtml($data);
        return $ret;
    }
    /**
     * Generate output for the response.
     *
     * @param $data
     * @return string
     */
    private function fetchHtml($data)
    {
        global $wpdb;
        $serverurl = 'https://' . $_SERVER['HTTP_HOST'] . '/';
        if (isset($data['customer']['emails']) && is_array($data['customer']['emails'])) {
            if (($key = array_search(CUSTOM_HELPSCOUT_EMAIL, $messages)) !== false) {
                unset($data['customer']['emails'][$key]);
            }
        } else {
            if ($data['customer']['email'] == CUSTOM_HELPSCOUT_EMAIL) {
                return 'Cannot query customer licenses.  E-mail from ' . CUSTOM_HELPSCOUT_EMAIL;
            }
        }
        //check if the user exist in user engage
        $html = '';
        // $data['customer']['email'] = 'basiliskan@gmail.com';
        $email      = $data['customer']['email'];
        $first_name = $data['customer']['fname'];
        $last_name  = $data['customer']['lname'];
        $userexist  = $this->findUserByEmail($email);
        //get all the lists
        $all_lists = $this->getAllLists();
        //get all the tags
        $all_tags = $this->getAllTags();
        if ($userexist) {

            $all_emails = $this->getUserEmailsById($userexist->id);
            $events = $this->getAllEvents($userexist->id);

            $temp       = explode(' ', $userexist->name);
            $first_name = $temp[0];
            $last_name  = $temp[1];
            $html.= '<a href="' . $serverurl . 'helpscout_userengage_action?action=removeuser&userid=' . $userexist->id . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8="> REMOVE USER</a><br>';
          
            $html.= $this->render('lists', array('userexist'=>$userexist, 'serverurl'=>$serverurl, 'all_lists'=>$all_lists));

            $html.= $this->render('tags', array('userexist'=>$userexist, 'serverurl'=>$serverurl, 'all_tags'=>$all_tags));

            $html.= $this->render('mails', array('all_emails'=>$all_emails));
            $html.= $this->render('events', array('events'=>$events));

            $profile_link = 'https://app.userengage.com/' . CUSTOM_HELPSOUCT_USERENGAGE_ADMIN_ID . '/user/' . $userexist->id;
            $html.= $this->render('existinguserdetails', array('profile_link'=>$profile_link, 'userexist'=>$userexist));

        } else {
            $html.= '<a href="' . $serverurl . 'helpscout_userengage_action?action=adduser&first_name=' . $first_name . '&last_name=' . $last_name . '&email=' . $email . '&v=AspxM5sEuZPdcDhAAM9f2kEcAn8=">ADD USER</a><br>';

           $html.= $this->render('lists', array('all_lists'=>$all_lists, 'first_name'=>$first_name,'last_name'=>$last_name,'email'=>$email)); 

            $html.= $this->render('tags', array('all_tags'=>$all_tags, 'first_name'=>$first_name,
                'last_name'=>$last_name,'email'=>$email));
        }
        return $html;
    }
    public function findUserByEmail($email)
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/search/?email=' . $email);
            $ue->setMethod('GET');
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function removeFromList($userId, $listId)
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/' . $userId . '/remove_from_list/');
            $ue->setMethod('POST');
            $ue->addField('list', $listId);
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function removeTag($userId, $tag)
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/' . $userId . '/remove_tag/');
            $ue->setMethod('POST');
            // Add the required fields:
            $ue->addField('name', $tag);
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function addToTag($tag, $userId = '', $newUser = false, $newUserEmail = '', $newUserFirstname = '', $newUserLastname = '')
    {
        if ($newUser == true && $userId == '') {
            $userId = $this->createUser($newUserEmail, $newUserFirstname, $newUserLastname);
        }
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/' . $userId . '/add_tag/');
            $ue->setMethod('POST');
            // Add the required fields:
            $ue->addField('name', $tag);
            $result = $ue->send();
            if ($result) {
                return $userId;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    public function addToList($listId, $userId = '', $newUser = false, $newUserEmail = '', $newUserFirstname = '', $newUserLastname = '')
    {
        if ($newUser == true && $userId == '') {
            $userId = $this->createUser($newUserEmail, $newUserFirstname, $newUserLastname);
        }
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/' . $userId . '/add_to_list/');
            $ue->setMethod('POST');
            // Add the required fields:
            $ue->addField('list', $listId);
            $result = $ue->send();
            if ($result) {
                return $userId;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    public function createUser($email, $firstname = '', $lastmame = '')
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/');
            $ue->setMethod('POST');
            $ue->addField('email', $email);
            $ue->addField('firstname', $firstname);
            $ue->addField('last_name', $lastmame);
            $result = $ue->send();
            if ($result->id) {
                return $result->id;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
    public function getAllLists()
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('lists/');
            $ue->setMethod('GET');
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function getAllTags()
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('tags/');
            $ue->setMethod('GET');
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function deleteUser($userId)
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/' . $userId . '/');
            $ue->setMethod('DELETE');
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function getAllEvents($userId)
    {
        try
        {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint('users/' . $userId . '/events/');
            $ue->setMethod('GET');
            $result = $ue->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    public function getUserEmailsById($userId)
    {
        $final    = array();
        $loop     = false;
        $endpoint = 'users/' . $userId . '/emails/';
        do {
            $ue = new CUSTOM_HELPSCOUT(CUSTOM_HELPSOUCT_USERENGAGE_API_KEY);
            $ue->setEndpoint($endpoint);
            $ue->setMethod('GET');
            $result = $ue->send();
            if (isset($result->results) && $result->results) {
                foreach ($result->results as $res) {
                    $final[] = $res;
                }
                if ($result->next != null && $result->next) {
                    $loop     = true;
                    $endpoint = str_replace('https://app.userengage.com/api/public/', '', $result->next);
                } else {
                    $loop = false;
                }
            }
        } while ($loop == true);
        return $final;
    }

    public function validateString($string)
    {
        if ($string == 'AspxM5sEuZPdcDhAAM9f2kEcAn8=') {
            return true;
        } else {
            return false;
        }
    }
    public static function render($view, $data = null)
    {
        // Handle data
        ($data) ? extract($data) : null;
    
        ob_start();
        include(plugin_dir_path(__FILE__).'../inc/'.$view.'.php');
        $view = ob_get_contents();
        ob_end_clean();

        return $view;
    }
}

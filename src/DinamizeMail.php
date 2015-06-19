<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Utility;

/**
 * Description of DinamizeMail
 *
 * @author fabricio.xavier
 */
class DinamizeMail {

    public function __construct($username, $password) {
        $this->params = array(
            'username' => $username,
            'password' => md5($password)
        );
        $this->ch = curl_init();
        $this->getToken();
    }

    public function hasAddedEmail($email) {
        $data = array(
            'page' => '1',
            'filter_list' => json_encode(array(array(
                    'name' => 'email',
                    'operator' => '=',
                    'value' => $email
        ))),
            'field_list' => json_encode(array('email'))
        );

        $response = json_decode($this->callService('/contact/search', $data));

        if (!empty($response->data_list)) {
            return true;
        }

        return false;
    }

    public function addEmail($email, $group_list, $fields = array()) {

        $fields = array_merge(array('email' => $email), (array) $fields);

        if ($this->hasAddedEmail($email)) {
            return false;
        }

        $data = array(
            'status_email' => 'OPTIN',
            'group_list' => json_encode((array) $group_list),
            'fields' => json_encode($fields)
        );

        return $response = $this->callService('/contact/create', $data);
    }

    private function getToken() {
        // refactorar - dry
        curl_setopt($this->ch, CURLOPT_URL, 'http://api.mail2easy.com.br/authenticate');
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->params));

        $response = curl_exec($this->ch);
        $code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($code != 200) {
            return false;
        }

        $this->response = json_decode($response);
        $this->token = $this->response->token;
        $this->service = $this->response->data_service;

        return $this->token;
    }

    private function callService($method, $fields = array()) {
        curl_setopt($this->ch, CURLOPT_URL, $this->service . $method);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Dinamize-Auth: ' . $this->token));
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $fields);

        return curl_exec($this->ch);
    }

}
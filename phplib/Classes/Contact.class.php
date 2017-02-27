<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Contact extends DataStore {
    
    const StoreDescription = 'Benchmarking community contact';

    public $baseXMLTag = 'Contact';
    public $defaultOp = 'entry';
    public $addDefault = true;
    public $storeData = '';   
    
    public $templateFieldDefaults = [
        'search' => [
            '_id' => 'Id',
            'surname' => 'Surname',
            'givenName' => 'Given Name',
            'email' => 'Email',
            'notes' => 'Notes'
        ],
    ];
    public $templateAllFields = [
            '_id' => 'Id',
            'surname' => 'Surname',
            'givenName' => 'Given Name',
            'email' => 'Email',
            'notes' => 'Notes',
    ];
    
    public $templateLinks = [
        '_id' => "<a href=\"##baseURL##/Contact/##_id##.html\">##_id##</a>",
    ];
    public $templateArrayLinks = [
        'CommunityList' => "<a href=\"##baseURL##/Community/##item##.html\">##item##</a>"
    ];
    
    public $classTemplate = 'file';

    
    function getData($params) {
        if (!isset($params->extended)) {
            $params->extended=0;
        }
        return $this->checkData(getContactData($params->id,$params->extended), $params->id);
    }
    
    static function info($params) {
        if (!isset($params->fmt)) {
            $params->fmt="json";
            $params->compact= false;
        }
        $data['Description'] = Contact::StoreDescription;        
        $data['Data'] = getContactInfo();
        return [STRUCT, $data];
    }
    
    function search($params) {
        if (!isset($params->queryOn)) {
            $params->queryOn = ["givenName" => 1, "_id" => 1, "surname" => 1, "notes" => 1];
        } else {
            $params->expand('queryOn', 'queryOn');
        }
        if (!isset($params->fields)) {
            $params->fields='';
        }
        if (isset($params->fmt) and preg_match('/htm/',$params->fmt)) {
            $this->template = new htmlTabTemplate();
        } else {
            $this->template = new TabTemplate();
        }        
        switch ($params->fields) {
            case 'ids':
                $params->fields = '_id';
                $this->template->setListFields(['_id' => 'Id'],$this->templateLinks);
                break;
            case 'all':
                 // TODO definir llista
                break;
            case '':
                $params->fields = "_id,surname,givenName,email,notes";
                $this->template->setListFields($this->templateFieldDefaults['search'],$this->templateLinks);
                break;
            default:
                $this->template->setListFields($this->templateAllFields,$this->templateLinks);
        }        
        $dataOut = searchContact((array) $params);        
        if (!isset($params->fmt)) {
            $params->fmt='tab';
        }
        switch ($params->fmt) {
            case 'tab':
                return [TEXT, $this->_formatTArray($params, $dataOut)];
                break;
            case 'html':
            case 'htm':
                return [HTML, $this->_formatHTML($params, $dataOut,'tab')];
                break;
            default:
                return [STRUCT, ['Communities' => $dataOut]];
        }
    }
}
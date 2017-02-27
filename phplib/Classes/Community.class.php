<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Community extends DataStore {
    
    const StoreDescription = 'Benchmarking communities';

    public $baseXMLTag = 'Commmunity';
    public $defaultOp = 'entry';
    public $addDefault = true;
    public $storeData = '';   
    
    public $templateFieldDefaults = [
        'search' => [
            '_id' => 'Acronym',
            'name' => 'Name',
            'status_id' => 'Status',
            'description' => 'Description',
            'linkMain' => 'URL'
        ],
    ];
    public $templateAllFields = [
        'search' => [
            '_id' => 'Acronym',
            'name' => 'Name',
            'status_id' => 'Status',
            'description' => 'Description',
            'linkMain' => 'URL'
        ]
    ];
    
    public $templateLinks = [
        '_id' => "<a href=\"##baseURL##/Community/##_id##.html\">##_id##</a>",
        'community_contacts' => "<a href=\"##baseURL##/Contact/##item##.html\">##item##</a>",
        'BenchmarkingEventsList' => "<a href=\"##baseURL##/BenchmarkingEvent/##item##.html\">##item##</a>",
        'DatasetList' => "<a href=\"##baseURL##/Dataset/##item##.html\">##item##</a>",        
    ];
    
    public $classTemplate = 'file';

    
    function getData($params) {
        if (!isset($params->extended)) {
            $params->extended=0;
        }
        return $this->checkData(getCommunityData($params->id,$params->extended), $params->id);
    }
    
    static function info($params) {
        if (!isset($params->fmt)) {
            $params->fmt="json";
            $params->compact= false;
        }
        $data['Description'] = Community::StoreDescription;        
        $data['Data'] = getCommunityInfo();
        return [STRUCT, $data];
    }
    
    function search($params) {
        if (!isset($params->queryOn)) {
            $params->queryOn = ["name" => 1, "_id" => 1, "description" => 1, "community_contacts" => 1];
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
                $this->template->setListFields(['_id' => 'Acronym'],$this->templateLinks);
                break;
            case 'all':
                 // TODO definir llista
                break;
            case '':
                $params->fields = "_id,name,status_id,description,linkMain";
                $this->template->setListFields($this->templateFieldDefaults['search'],$this->templateLinks);
                break;
            default:
                $this->template->setListFields($this->templateAllFields,$this->templateLinks);
        }        
        $dataOut = searchCommunity((array) $params);        
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

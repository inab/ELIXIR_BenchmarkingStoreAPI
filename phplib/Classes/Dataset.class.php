<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Dataset extends DataStore {
    
    const StoreDescription = 'Benchmarking Datasets';

    public $baseXMLTag = 'Dataset';
    public $defaultOp = 'entry';
    public $addDefault = true;
    public $storeData = '';   
    
    public $templateFieldDefaults = [
        'search' => [
            "_id"=> "Community:Id",
            "name" => "Name",
            "type" => "type",
            "description"=> "Description",
        ]
    ];
    public $templateAllFields = [
            "_id"=> "Community:Id",
            "name" => "Name",
            "type" => "type",
            "description"=> "Description",
    ];
    
    public $templateLinks = [
        '_id' => "<a href=\"##baseURL##/Dataset/##_id##.html\">##_id##</a>",
    ];
    public $templateArrayLinks = [
        'dataset_contact_id' => 'API:Contact',
        'Events' =>             'API:BenchmarkingEvent',
        'testEventList'=>          'API:TestEvent'
    ];
    
    public $classTemplate = 'file';
    public $textQueryOn = ['_id'=>1,'name'=>1,'description'=>1];
    
    function getData($params, $checkId=true) {
        $data = parent::getData($params);
        if ($this->error) 
            {return '';}
        if (isset($params->extended) and $params->extended) {
            $data['contacts']=  findArrayInDataStore('Contact', $data['dataset_contact_id']);
            $data['references'] = findArrayInDataStore('Reference', $data['references']);
        }        
        $testEvents = iterator_to_array(findInDataStore('TestEvent', 
                [
                    '$or' =>
                        [
                          ['input_dataset_id'=> $data['_id']],
                          ['output_dataset_id'=> $data['_id']]
                        ]
                ], []));
        foreach ($testEvents as $te) {
            $data['Events'][]=$te['benchmarking_event_id'];
            $data['testEventList'][] = $te['_id'];
        }
        $data['Events']=array_values(array_unique($data['Events']));
        if (preg_match("/htm/",$params->fmt)) {
        //Metrics  Inline HTML, TODO nested templates
            if (isset($data['metrics'])) {
                foreach ($data['metrics'] as $m) {
                    $lin = "<a href='".$GLOBALS['baseURL']."/Metrics/".$m['metrics_id'].".html'>".$m['metrics_id']."</a><br> ";
                    foreach (array_keys($m['result']) as $mm) {
                        $lin .= "<a href='".$GLOBALS['baseURL']."/Tool/$mm.html'>$mm</a>: ".$m['result'][$mm]." ";
                    }
                    $dataMetricsTxt[]=$lin;
                };
            } else {
                $dataMetricsTxt =[];
            }
            $data['metricsTab'] = "<table><tr>".join("</tr><tr>",$dataMetricsTxt)."</tr></table>";
        }
        
        return $data;
    }
}

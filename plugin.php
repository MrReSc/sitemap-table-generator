<?php
    class pluginSitemapTableGen extends Plugin {

        public function beforeAll() {
		    $webhook = 'sitemap-table';
            $keys = array('title', 'description', 'dateRaw', 'username', 'category', 'permalink');
		    if ($this->webhook($webhook)) {
                
                if (file_exists($_SERVER['DOCUMENT_ROOT'].'/siteindex/pages.json')) {    

                    // get json file
                    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/siteindex/pages.json');
                    // decode json 
                    $obj = json_decode($json);
                    // get data array
                    $data = $obj->data;

                    // create table and header
                    $html  = '<table class="sortable">';
                    $html .= '<thead>';
                    $html .= '<tr>';

                    foreach($data[0] as $key=>$value){
                        if (in_array($key, $keys)) {
                            $html .= '<th>' . $key . '</th>';
                        }
                    }

                    $html .= '</tr>';
                    $html .= '</thead>';

                    // create table body
                    $html .= '<tbody>';
                    foreach($data as $article){
                        $html .= '<tr>';
                        foreach($article as $key=>$value){
                            if (in_array($key, $keys)) {
                                if (strpos($key, 'link') !== false) {
                                    $html .= '<td>';
                                    $html .= '<a href="' . $value . '">' . $value . '</a>';
                                    $html .= '</td>';
                                } else {
                                    $html .= '<td>' . $value . '</td>';
                                }                       
                            }                   
                        }
                        $html .= '</tr>';
                    }
                    
                    // close table tags
                    $html .= '</tbody>';
                    $html .= '</table>';
                    $html .= '<script src="' . $this->htmlPath() .'sort-table.js' . '"></script>';
                    echo $html;
                }
                
		    }
	    }
    }
?>
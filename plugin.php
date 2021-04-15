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

                    // add search bar
                    $html  = '<div id="posts">';
                    $html .= '<input class="search" placeholder="Search" />';

                    // create table and header
                    $html .= '<table>';
                    $html .= '<thead>';
                    $html .= '<tr>';

                    foreach($data[0] as $key=>$value){
                        if (in_array($key, $keys)) {
                            $html .= '<th>';
                            $html .= '<button class="sort" data-sort="' . $key . '">' . $key . '</button>';
                            $html .= '</th>';
                        }
                    }

                    $html .= '</tr>';
                    $html .= '</thead>';

                    // create table body
                    $html .= '<tbody class="list">';
                    foreach($data as $article){
                        $html .= '<tr>';
                        foreach($article as $key=>$value){
                            if (in_array($key, $keys)) {
                                if (strpos($key, 'link') !== false) {
                                    $html .= '<td class="' . $key . '">';
                                    $html .= '<a href="' . $value . '">' . $value . '</a>';
                                    $html .= '</td>';
                                } else {
                                    $html .= '<td class="' . $key . '">' . $value . '</td>';
                                }                       
                            }                   
                        }
                        $html .= '</tr>';
                    }
                    
                    // close table tags
                    $html .= '</tbody>';
                    $html .= '</table>';
                    $html .= '</div>';

                    // add JS
                    $html .= '<script src="' . $this->htmlPath() .'list.min.js' . '"></script>';

                    // render-js
                    $html .= '<script id="rendered-js">';
                    $html .= 'var options = { valueNames: [ ';           
                    foreach($data[0] as $key=>$value){
                        if (in_array($key, $keys)) {
                            $html .= "'" . $key . "', ";
                        }
                    }
                    $html .= '] }; ';
                    $html .= "var userList = new List('posts', options);";
                    $html .= '</script>';

                    // print html out
                    echo $html;
                }
                
		    }
	    }
    }
?>
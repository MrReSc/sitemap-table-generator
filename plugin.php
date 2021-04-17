<?php
    class pluginSitemapTableGen extends Plugin {

        public function init()
        {
            $this->dbFields = array(
                'headerSitemap'=>'title Titel|description Beschreibung|dateRaw Datum|username Autor|category Kategorie|permalink Link',
                'pagesJsonPath'=>'/siteindex/pages.json'
            );
        }

        public function form()
        {
            global $L;
    
            $html  = '<div class="alert alert-primary" role="alert">';
            $html .= $this->description();
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<label>'.$L->get('pages-json-path').'</label>';
            $html .= '<input name="pagesJsonPath" id="jspagesJsonPath" type="text" value="'.$this->getValue('pagesJsonPath').'">';
            $html .= '<span class="tip">'.$L->get('pages-json-path-explanation').'</span>';
            $html .= '</div>';
    
            $html .= '<div>';
            $html .= '<label>'.$L->get('header-sitemap').'</label>';
            $html .= '<input name="headerSitemap" id="jsheaderSitemap" type="text" value="'.$this->getValue('headerSitemap').'">';
            $html .= '<span class="tip">'.$L->get('header-sitemap-example').'</span>';
            $html .= '</div>';
    
            return $html;
        }

        public function beforeSiteLoad() {
		    $webhook = 'sitemap';
		    if ($this->webhook($webhook)) {
                    $html = '<head>';
                    $html .= '<title>Sitemap</title>';
                    $html .= '</head>';
                    // print html out
                    echo $html;
            }
        }

        public function pageBegin() {
		    $webhook = 'sitemap';
            $keys = array();
            $langkeys = array();

		    if ($this->webhook($webhook)) {

                $headerSitemap = explode("|", $this->getValue('headerSitemap'));
                foreach($headerSitemap AS $head) {
                    $values = explode(" ", $head);
                    array_push($keys, $values[0]);
                    array_push($langkeys, $values[1]);
                }
                
                if (file_exists($_SERVER['DOCUMENT_ROOT'].$this->getValue('pagesJsonPath'))) {    

                    // get json file
                    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/siteindex/pages.json');
                    // decode json 
                    $obj = json_decode($json);
                    // get data array
                    $data = $obj->data;

                    echo '<style type="text/css">';
                    include 'css/table.css';
                    echo '</style>';

                    // add search bar
                    $html .= '<div id="posts">';
                    $html .= '<input class="search" placeholder="Suche" />';

                    // create table and header
                    $html .= '<table>';
                    $html .= '<thead>';
                    $html .= '<tr>';


                    foreach($keys as $index=> $key){
                        if (in_array($key, $keys)) {
                            $html .= '<th>';
                            $html .= '<button class="sort" data-sort="' . $key . '-cell">' . $langkeys[$index] . '&nbsp;&#x21D5;</button>';
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
                                    $html .= '<td class="' . $key . '-cell">';
                                    $html .= '<a href="' . $value . '">' . $value . '</a>';
                                    $html .= '</td>';
                                } else {
                                    $html .= '<td class="' . $key . '-cell">' . $value . '</td>';
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
                    $html .= '<script src="' . $this->htmlPath() .'/list/list.min.js' . '"></script>';

                    // render-js
                    $html .= '<script id="rendered-js">';
                    $html .= 'var options = { valueNames: [ ';           
                    foreach($data[0] as $key=>$value){
                        if (in_array($key, $keys)) {
                            $html .= "'" . $key . "-cell', ";
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

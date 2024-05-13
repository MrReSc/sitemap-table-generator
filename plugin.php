<?php
class pluginSitemapTableGen extends Plugin {

    public function init()
    {
        $this->dbFields = array(
            'headerSitemap'=>'title Titel|description Beschreibung|dateRaw Datum|username Autor|category Kategorie|permalink Link',
            'pagesJsonPath'=>'/siteindex/pages.json',
            'webhookSitemap'=>'sitemap',
            'pageTitle'=>'Sitemap table',
            'enableSearchbar'=>true,
            'enableColumnSort'=>true,
            'linkColumn' => 'permalink',
            'enableSidebarSearch'=>false,
        );
    }

    public function beforeAll(){
        global $url;
        $webhook = $this->getValue('webhookSitemap');
        if ($this->webhook($webhook)) {
            // prevent a 404 status code
            $url->setSlug(false);
        }
    }

    public function beforeSiteLoad() {
        global $page;
        $webhook = $this->getValue('webhookSitemap');
        if ($this->webhook($webhook)) {
            $page->setField("type","static");
            if (!empty($this->getValue('pageTitle'))){
                $page->setField("title",$this->getValue('pageTitle'));
            }
            else {
                $page->setField($webhook);
            }
        }
    }

    public function form()
    {
        global $L;

        $html  = '<div class="alert alert-primary" role="alert">';
        $html .= $this->description();
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('webhook-sitemap').'</label>';
        $html .= '<input name="webhookSitemap" id="jswebhookSitemap" type="text" value="'.$this->getValue('webhookSitemap').'">';
        $html .= '<span class="tip">'.$L->get('webhook-sitemap-explanation').'</span>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('page-title').'</label>';
        $html .= '<input name="pageTitle" id="jspageTitle" type="text" value="'.$this->getValue('pageTitle').'">';
        $html .= '<span class="tip">'.$L->get('page-title-example').'</span>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('header-sitemap').'</label>';
        $html .= '<input name="headerSitemap" id="jsheaderSitemap" type="text" value="'.$this->getValue('headerSitemap').'">';
        $html .= '<span class="tip">'.$L->get('header-sitemap-example').'</span>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('enable-searchbar').'</label>';
        $html .= '<select name="enableSearchbar">';
        $html .= '<option value="true" '.($this->getValue('enableSearchbar')==true?'selected':'').'>'.$L->get('Enabled').'</option>';
        $html .= '<option value="false" '.($this->getValue('enableSearchbar')==false?'selected':'').'>'.$L->get('Disabled').'</option>';
        $html .= '</select>';
        $html .= '<span class="tip">'.$L->get('enable-searchbar-example').'</span>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('enable-column-sort').'</label>';
        $html .= '<select name="enableColumnSort">';
        $html .= '<option value="true" '.($this->getValue('enableColumnSort')===true?'selected':'').'>'.$L->get('Enabled').'</option>';
        $html .= '<option value="false" '.($this->getValue('enableColumnSort')===false?'selected':'').'>'.$L->get('Disabled').'</option>';
        $html .= '</select>';
        $html .= '<span class="tip">'.$L->get('enable-column-sort-example').'</span>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('link-column').'</label>';
        $html .= '<select name="linkColumn">';
        $html .= '<option value="permalink" '.($this->getValue('linkColumn')==='permalink'?'selected':'').'>'.$L->get('permalink').'</option>';
        $html .= '<option value="title" '.($this->getValue('linkColumn')==='title'?'selected':'').'>'.$L->get('title').'</option>';
        $html .= '</select>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>'.$L->get('enable-sidebar-search').'</label>';
        $html .= '<select name="enableSidebarSearch">';
        $html .= '<option value="true" '.($this->getValue('enableSidebarSearch')===true?'selected':'').'>'.$L->get('Enabled').'</option>';
        $html .= '<option value="false" '.($this->getValue('enableSidebarSearch')===false?'selected':'').'>'.$L->get('Disabled').'</option>';
        $html .= '</select>';
        $html .= '</div>';



        return $html;
    }

    private function getPages($args)
    {
        global $pages;
        global $tags;

        // Parameters and the default values
        $published 	= (isset($args['published'])?$args['published']=='true':true);
        $static 	= (isset($args['static'])?$args['static']=='true':false);
        $draft 		= (isset($args['draft'])?$args['draft']=='true':false);
        $sticky 	= (isset($args['sticky'])?$args['sticky']=='true':false);
        $scheduled 	= (isset($args['scheduled'])?$args['scheduled']=='true':false);

        $numberOfItems = (isset($args['numberOfItems'])?$args['numberOfItems']:10);
        $pageNumber = (isset($args['pageNumber'])?$args['pageNumber']:1);
        $list = $pages->getList($pageNumber, $numberOfItems, $published, $static, $sticky, $draft, $scheduled);

        $tmp = array(
            'status'=>'0',
            'message'=>'List of pages',
            'numberOfItems'=>$numberOfItems,
            'data'=>array()
        );
        foreach ($list as $pageKey) {
            try {
                // Create the page object from the page key
                $page = new Page($pageKey);
                $articleData = $page->json($returnsArray=true);
                $tagLabels = explode(',',trim($articleData['tags']));
                $articleData['tags'] = implode(', ',$tagLabels);
                $tagLinks = array();
                foreach ($tagLabels as $label) {
                    if(strlen($label) > 0) {
                        $key = Text::cleanUrl($label);
                        $tagLinks[] = '<a href="/tag/' . $key . '">' . $label . '</a>';
                    }
                }

                $articleData['tagLinks'] = implode(', ',$tagLinks);
                array_push($tmp['data'], $articleData);

            } catch (Exception $e) {
                // Continue
            }
        }
        return $tmp;
    }

    public function pageBegin() {
        $webhook = $this->getValue('webhookSitemap');
        $keys = array();
        $langkeys = array();
        global $users;

        if ($this->webhook($webhook)) {
            $headerSitemap = explode("|", $this->getValue('headerSitemap'));
            foreach($headerSitemap AS $head) {
                $values = explode(" ", $head);
                array_push($keys, $values[0]);
                array_push($langkeys, $values[1]);
            }



            $obj = $this->getPages(array("numberOfItems"=>10000));
            $data = $obj["data"];
            $data[0]['nickname'] = "";
            echo '<style type="text/css">';
            include 'css/table.css';
            echo '</style>';

            // add title
            if (!empty($this->getValue('pageTitle'))){
                $html .= '<h1>' . $this->getValue('pageTitle') . '</h1>';
            }

            // add search bar
            if ($this->getValue('enableSearchbar') === true) {
                $html .= '<div id="posts">';
                $value = "";
                if(strlen($_GET['search']) > 1){
                    $value = 'value="'.strip_tags($_GET['search']).'"';
                }
                $html .= '<input class="search" placeholder="Suche" '.$value.' />';
                $html .= '<div id="posts-inside">';
            }

            // create table and header
            $html .= '<table>';
            $html .= '<thead>';
            $html .= '<tr>';

            foreach($keys as $index=> $key){
                $hidden = '';
                if (array_key_exists($key, $data[0])){
                    if(strpos($key,'$')){
                        $hidden = 'style="display:none"';
                        $key = str_replace('$','',$key);
                    }
                    $html .= '<th '.$hidden.'>';
                    if ($this->getValue('enableColumnSort') === true) {
                        $html .= '<button class="sort" data-sort="' . $key . '-cell">' . $langkeys[$index] . '&nbsp;&#x21D5;</button>';
                    }
                    else {
                        $html .= $langkeys[$index];
                    }
                    $html .= '</th>';
                }
            }

            $html .= '</tr>';
            $html .= '</thead>';

            // create table body
            $html .= '<tbody class="list">';
            foreach($data as $article){
                $user = $users->getUserDB($article['username']);
                $article['nickname'] = $user['nickname'];
                $html .= '<tr>';
                foreach($keys as $key){
                    $hidden = '';
                    if(strpos($key,'$')){
                        $hidden = 'style="display:none"';
                        $key = str_replace('$','',$key);
                    }
                    if (array_key_exists($key, $article)) {
                        if ($key == $this->getValue('linkColumn')) {
                            $html .= '<td class="' . $key . '-cell" '.$hidden .'>';
                            $html .= '<a href="' . $article["permalink"]. '">' . $article[$key] . '</a>';
                            $html .= '</td>';
                        } else {
                            $html .= '<td class="' . $key . '-cell">' . $article[$key] . '</td>';
                        }
                    }
                }
                $html .= '</tr>';
            }

            // close table tags
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '</div>';


            // add JS
            $html .= '<script src="' . $this->htmlPath() .'/list/list.min.js' . '"></script>';

            // render-js
            $html .= '<script id="rendered-js">';
            $html .= 'var options = { valueNames: [ ';
            foreach($data[0] as $key=>$value){
                if (in_array($key, $keys) || in_array($key.'$', $keys)){
                    $html .= "'" . $key . "-cell', ";
                }
            }
            $html .= '] }; ';
            $html .= "var userList = new List('posts', options);";
            if(strlen($_GET['search']) > 1){
                $html .= "userList.search('".str_replace("'","\'",strip_tags($_GET['search']))."');";
            }
            $html .= '</script>';

            // print html out
            echo $html;


        }
    }

    // HTML for sidebar
    public function siteSidebar()
    {
        if ($this->getValue('enableSidebarSearch')){
            global $L;

            $html = '<div class="plugin plugin-sitemap-table-generator">';
            $html .= '<div class="plugin-content">';
            $html .= '<form method="get" action="/' . $this->getValue('webhookSitemap') . '">';
            $html .= '<input type="text" name="search" /> ';

            $html .= '<input type="submit" value="' . $L->get('Search') . '" />';


            $html .= '</form>';
            $html .= '</div>';
            $html .= '</div>';


            return $html;
        }
        return '';
    }
}
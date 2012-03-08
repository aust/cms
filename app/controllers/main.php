<?php if (!defined('INDIRECT')) die();
class ControllerMain extends ControllerTemplate
{
    public $template = 'main';
    
    public function Before()
    {
        parent::Before();
        
        // Generate menus for the page.
        $this->template->Variable('menu_items', 
            Database::current()
                ->Query("SELECT * FROM `cms_menu` JOIN `cms_pages` "
                    . "ON `cms_menu`.`page_id`=`cms_pages`.`page_id` "
                    . "WHERE `cms_pages`.`published`=1 "
                    . "ORDER BY `cms_menu`.`position` ASC;")
                ->FetchArray());
    }
    
    public function ActionIndex()
    {
        /*
        $page = Database::current()
                    ->Query("SELECT * FROM `cms_pages` WHERE `default`=1")
                    ->Fetch();
        
        $this->request->Redirect($page['slug']);
         * 
         */
        
        $user = new User(array(
                'username' => 'A$',
            ));
        
        $content = '';
        
        if (!$user->Validate())
        {
            $content = print_r($user->errors(), true);
        }
        
        $this->template->Variables(array(
            'page_title' => 'Test', 
            'content' => $content,));
    }
    
    public function ActionPage()
    {
        // Select page based on slug name.
        $page = Database::current()
                    ->Query("SELECT * FROM `cms_pages` WHERE `slug`='"
                        . $this->request->parameter('slug') . "' LIMIT 1")
                    ->Fetch();
        
        // Add template information based on page.
        $this->template->Variables(array(
            'page_title' => $page['title'], 
            'content' => $page['content'],));
    }
}
?>

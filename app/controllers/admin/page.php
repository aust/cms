<?php if (!defined('INDIRECT')) die();

ControllerTemplate::Load('admin/admin');

class ControllerAdminPage extends ControllerAdmin
{
    public function Before()
    {
        parent::Before();
        
        // If a user can't edit pages, then they cannot even add/delete/publish/unpublish pages.
        if (!$this->user['permission_pages_edit'])
            $this->request->Redirect('admin/status/access');
    }
    
    public function ActionIndex()
    {
        $this->request->Redirect('admin/page/list');
    }
    
    public function ActionList()
    {
        $list_view = View::Factory('admin/page/list', array(
                'user'  => $this->user,
                'pages' => Database::current()
                               ->Query('SELECT * FROM `cms_pages`')
                               ->FetchArray(),
            ));
        
        // Get a possible status message
        $status = $this->request->parameter('status');
        
        switch ($status)
        {
            case 'added':
                $list_view->Variable('status_message', 'Page has been successfully added.');
                break;
            
            case 'deleted':
                $list_view->Variable('status_message', 'Page has been successfully deleted.');
                break;
            
            case 'published':
                $list_view->Variable('status_message', 'Page has been successfully archived.');
                break;
            
            case 'unpublished':
                $list_view->Variable('status_message', 'Page has been successfully unarchived.');
                break;
            
            case 'not-found':
                $list_view->Variable('status_message', 'Page cannot be found.');
                break;
            
            default:
                if ($status !== null)
                    $list_view->Variable('status_message', 'Unknown status: ' . $status);
                break;
        }
        
        $this->template->Variables(array(
                'page_title' => 'Manage Pages',
                'content' => $list_view,
            ));
    }
    
    public function ActionNew()
    {
        
    }
    
    public function ActionNewSave()
    {
        
    }
    
    public function ActionEdit()
    {
        $page_id = $this->request->parameter('page_id');
        
        $page = Database::current()
                    ->Query('SELECT * FROM `cms_pages` WHERE `page_id`=\''
                        . Database::current()->Escape($page_id) . '\'')
                    ->Fetch();
        
        // Page does not exist
        if (!$page)
            $this->request->Redirect('admin/page/list/status/not-found');
        
        $head_view = View::Factory('admin/page/head');
        $edit_view = View::Factory('admin/page/edit', array(
                'page' => $page,
            ));
        
        $status = $this->request->parameter('status');
        
        switch ($status)
        {
            case 'title':
                $edit_view->Variable('status_message', 'Invalid title.');
                break;
        }
        
        $this->template->Variables(array(
                'head' => $head_view,
                'page_title' => 'Editing Page',
                'content' => $edit_view,
            ));
    }
    
    public function ActionEditSave()
    {
        $page_id = $this->request->parameter('page_id');
        
        $page = Database::current()
                    ->Query('SELECT `page_id` FROM `cms_pages` WHERE `page_id`=\''
                        . Database::current()->Escape($page_id) . '\'')
                    ->Fetch();
        
        // Page does not exist
        if (!$page)
            $this->request->Redirect('admin/page/list/status/not-found');
        
        $title = $this->request->post('title');
        $content = $this->request->post('content');
        
        if (strlen($title) <= 0)
            $this->request->Redirect('admin/page/edit/'
                . $page_id . '/status/title');
        
        // TODO: Check for database errors
        Database::current()
            ->Query('UPDATE `cms_pages` SET '
                . '`title`=\'' . Database::current()->Escape($title) . '\', '
                . '`content`=\'' . Database::current()->Escape($content) . '\', '
                . '`slug`=\'' . Database::current()->Escape(Slug($title)) . '\' '
                . 'WHERE `page_id`=\'' . Database::current()->Escape($page_id) . '\'')
            ->Execute();
    }
    
    public function ActionPublish()
    {
        
    }
    
    public function ActionUnpublish()
    {
        
    }
    
    public function ActionDelete()
    {
        
    }
    
    public function ActionDeleteConfirmed()
    {
        
    }
}
?>

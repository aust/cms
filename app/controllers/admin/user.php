<?php if (!defined('INDIRECT')) die();

ControllerTemplate::Load('admin/admin');

class ControllerAdminUser extends ControllerAdmin
{
    public function ActionIndex()
    {
        $this->request->Redirect('admin/user/list');
    }
    
    public function ActionList()
    {
        $users = Database::current()
                     ->Query('SELECT * FROM `cms_users`')
                     ->FetchArray();
        
        $status_message = null;
        switch ($this->request->parameter('status'))
        {
            case 'added':
                $status_message = 'User has been successfully added.';
                break;
            
            case 'deleted':
                $status_message = 'User has been successfully deleted.';
                break;
            
            case 'delete-current':
                $status_message = 'Users cannot delete themselves.';
                break;
            
            case 'not-found':
                $status_message = 'User cannot be found.';
                break;
            
            default:
                $status_message = $this->request->parameter('status');
                break;
        }
        
        $this->template->Variables(array(
                'page_title' => 'Manage Users',
                'content' => View::Factory('admin/user/list', array(
                        'status_message' => $status_message,
                        'users' => $users,
                    )),
            ));
    }
    
    public function ActionNew()
    {
        $this->template->Variables(array(
                'page_title' => 'Add New User',
                'content' => View::Factory('admin/user/new'),
            ));
    }
    
    public function ActionNewSave()
    {
        if (!$this->request->post('save'))
            $this->request->Redirect('admin/user/new');
        
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $confirm_password = $this->request->post('confirm_password');
        $email = $this->request->post('email');
        $first_name = $this->request->post('first_name');
        $last_name = $this->request->post('last_name');
        $permission_manage_users = $this->request->post('permission_manage_users');
        $permission_pages_edit = $this->request->post('permission_pages_edit');
        $permission_pages_add = $this->request->post('permission_pages_add');
        $permission_blog_entry_edit = $this->request->post('permission_blog_entry_edit');
        $permission_blog_entry_add = $this->request->post('permission_blog_entry_add');
        $permission_blog_entry_credit_users = $this->request->post('permission_blog_entry_credit_users');
        
        // TODO: Check for valid input
        $error = false;
        
        if (strlen($username) <= 0)
            $error = true;
        
        if (strlen($password) <= 0 || strlen($confirm_password) <= 0 ||
            $password != $confirm_password)
            $error = true;
        
        // Check if user exists
        $user = Database::current()
                    ->Query('SELECT * FROM `cms_users` WHERE '
                        . '`username`=\'' . Database::current()->Escape($username) . '\' OR '
                        . '`email`=\'' . Database::current()->Escape($email) . '\'')
                    ->Fetch();
        
        if (!$error && !$user)
        {
            Database::current()
                ->Query('INSERT INTO `cms_users`(`username`,`password`,`email`,'
                    . '`first_name`,`last_name`,`permission_manage_users`,'
                    . '`permission_pages_edit`,`permission_pages_add`,'
                    . '`permission_blog_entry_edit`,`permission_blog_entry_add`,'
                    . '`permission_blog_entry_credit_users`)'
                    . 'VALUES('
                    . '\'' . Database::current()->Escape($username) . '\', '
                    . '\'' . sha1($password) . '\', '
                    . '\'' . Database::current()->Escape($email) . '\', '
                    . '\'' . Database::current()->Escape($first_name) . '\', '
                    . '\'' . Database::current()->Escape($last_name) . '\', '
                    . (('true' == $permission_manage_users) ? 1 : 0) . ', '
                    . (('true' == $permission_pages_edit) ? 1 : 0) . ', '
                    . (('true' == $permission_pages_add) ? 1 : 0) . ', '
                    . (('true' == $permission_blog_entry_edit) ? 1 : 0) . ', '
                    . (('true' == $permission_blog_entry_add) ? 1 : 0) . ', '
                    . (('true' == $permission_blog_entry_credit_users) ? 1 : 0)
                    . ')')
                ->Execute();
            
            $this->request->Redirect('admin/user/list/status/added');
        }
        
        $this->request->Redirect('admin/user/new');
    }
    
    public function ActionEdit()
    {
        $user_id = $this->request->parameter('user_id');
        
        $user = Database::current()
                     ->Query('SELECT * FROM `cms_users` WHERE `user_id`=\''
                         . Database::current()->Escape($user_id) . '\' LIMIT 1')
                     ->Fetch();
        
        // TODO: Redirect to a status saying user doesn't exist
        if (!$user)
            $this->request->Redirect('admin/user/list/status/not-found');
        
        $edit_view = View::Factory('admin/user/edit', array('edit_user' => $user));
        
        switch ($this->request->parameter('status'))
        {
            case 'saved':
                $edit_view->Variable('status_message', 'Changes has been successfully saved.');
                break;
            
            case 'username':
                $edit_view->Variable('status_message', 'Username is invalid.');
                break;
            
            case 'password':
                $edit_view->Variable('status_message', 'Passwords do not match.');
                break;
        }
        
        $this->template->Variables(array(
                'page_title' => 'Managing User ' . $user['username'],
                'content' => $edit_view,
            ));
    }
    
    public function ActionEditSave()
    {
        $user_id = $this->request->parameter('user_id');
        
        // Don't continue to save if the user didn't initiate submission of save
        // data for the specific user.
        if (!$this->request->post('save'))
            $this->request->Redirect('admin/user/edit/' . $user_id);
        
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $confirm_password = $this->request->post('confirm_password');
        $email = $this->request->post('email');
        $first_name = $this->request->post('first_name');
        $last_name = $this->request->post('last_name');
        $permission_manage_users = $this->request->post('permission_manage_users');
        $permission_pages_edit = $this->request->post('permission_pages_edit');
        $permission_pages_add = $this->request->post('permission_pages_add');
        $permission_blog_entry_edit = $this->request->post('permission_blog_entry_edit');
        $permission_blog_entry_add = $this->request->post('permission_blog_entry_add');
        $permission_blog_entry_credit_users = $this->request->post('permission_blog_entry_credit_users');
        
        $error = false;
        
        $update_password = false;
        
        if (strlen($username) <= 0)
            $this->request->Redirect('admin/user/edit/' . $user_id . '/status/username');
        
        if (strlen($password) > 0 && strlen($confirm_password) > 0)
            if ($password != $confirm_password)
                $this->request->Redirect('admin/user/edit/' . $user_id . '/status/password');
            else
                $update_password = true;
        
        if (!$error)
        {
            Database::current()
                ->Query('UPDATE `cms_users` SET '
                    . '`username`=\'' . Database::current()->Escape($username) . '\', '
                    . (($update_password) ? '`password`=\'' . sha1($password) . '\', ' : '')
                    . '`email`=\'' . Database::current()->Escape($email) . '\', '
                    . '`first_name`=\'' . Database::current()->Escape($first_name) . '\', '
                    . '`last_name`=\'' . Database::current()->Escape($last_name) . '\', '
                    . '`permission_manage_users`=' . (($permission_manage_users == 'true') ? 1 : 0) . ', '
                    . '`permission_pages_edit`=' . (($permission_pages_edit == 'true') ? 1 : 0) . ', '
                    . '`permission_pages_add`=' . (($permission_pages_add == 'true') ? 1 : 0) . ', '
                    . '`permission_blog_entry_edit`=' . (($permission_blog_entry_edit == 'true') ? 1 : 0) . ', '
                    . '`permission_blog_entry_add`=' . (($permission_blog_entry_add == 'true') ? 1 : 0) . ', '
                    . '`permission_blog_entry_credit_users`=' . (($permission_blog_entry_credit_users == 'true') ? 1 : 0) . ' '
                    . 'WHERE `user_id`=\'' . Database::current()->Escape($user_id) . '\'')
                ->Execute();
        }
        
        $this->request->Redirect('admin/user/edit/' . $user_id . '/status/saved');
    }
    
    public function ActionDelete()
    {
        $user_id = $this->request->parameter('user_id');
        
        // Check if user exists
        $user = Database::current()
                     ->Query('SELECT * FROM `cms_users` WHERE `user_id`=\''
                         . Database::current()->Escape($user_id) . '\' LIMIT 1')
                     ->Fetch();
        
        // TODO: Redirect to a status saying user doesn't exist
        if (!$user)
            $this->request->Redirect('admin/user/list/status/not-found');
        
        // TODO: Redirect to a status saying the user can't delete themselves.
        if ($user_id == $this->user['user_id'])
            $this->request->Redirect('admin/user/list/status/delete-current');
        
        $this->template->Variables(array(
                'page_title' => 'Confirm Deletion of ' . $user['username'],
                'content' => View::Factory('admin/user/delete', array(
                        'delete_user' => $user,
                    )),
            ));
    }
    
    public function ActionDeleteConfirmed()
    {
        $user_id = $this->request->parameter('user_id');
        
        // Check if user exists
        $user = Database::current()
                     ->Query('SELECT * FROM `cms_users` WHERE `user_id`=\''
                         . Database::current()->Escape($user_id) . '\' LIMIT 1')
                     ->Fetch();
        
        // TODO: Redirect to a status saying user doesn't exist
        if (!$user)
            $this->request->Redirect('admin/user/list/status/not-found');
        
        // TODO: Redirect to a status saying the user can't delete themselves.
        if ($user_id == $this->user['user_id'])
            $this->request->Redirect('admin/user/list/status/delete-current');
        
        // TODO: Delete not only the user, but all information they are tied to.
        // TODO: Check for MySQL errors.
        Database::current()
            ->Query('DELETE FROM `cms_users` WHERE `user_id`=\''
                . Database::current()->Escape($user_id) . '\'')
            ->Execute();
        
        // TODO: Redirect to a status saying user deletion is successful
        $this->request->Redirect('admin/user/list/status/deleted');
    }
}
?>
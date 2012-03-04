<?php if (!defined('INDIRECT')) die();
class ControllerAdmin extends Controller
{
    public $user = null;
    public $auth = null;
    
    public function Before()
    {
        parent::Before();
        
        $this->auth = Auth::Factory();
        $this->user = $this->auth->user();
        
        if (!$this->user && !preg_match(',^' . CMS::base_url()
            . 'admin/auth(/(.*))?$,', $this->request->uri()))
            $this->request->Redirect('admin/auth/status/access');
    }
    
    public function ActionIndex()
    {
        $this->response->body(View::Factory('admin', array(
                'page_title' => 'Admin',
                'content' => 'Admin section',
            )));
    }
    
    public function ActionAuth()
    {
        if ($this->user)
            $this->request->Redirect('admin');
        
        $this->response->body(View::Factory('admin/auth', array(
                'page_title' => 'Authentication',
                'content' => 'You must authenticate before proceeding.',
            )));
    }
    
    public function ActionAuthLogin()
    {
        if ($this->user)
            $this->request->Redirect('admin');
        
        $this->user = $this->auth->Authenticate('admin', 'password');
        
        if (!$this->user)
            $this->request->Redirect('admin/auth/status/failed');
        else
            $this->request->Redirect('admin');
    }
    
    public function ActionAuthLogout()
    {
        $this->auth->Deauthenticate();
        $this->request->Redirect('admin/auth/status/logged-out');
    }
    
    public function ActionAuthStatus()
    {
        if ($this->user)
            $this->request->Redirect('admin');
        
        $auth_status = 'Unknown error. Please try again later. [' . $this->request->parameter('auth_status') . ']';
        switch ($this->request->parameter('auth_status'))
        {
            case 'failed':
                $auth_status = 'Username or password is incorrect. Please try again.';
                break;
            
            case 'access':
                $auth_status = 'You must be authenticated to proceed.';
                break;
            
            case 'logged-out':
                $auth_status = 'You have been successfully logged out.';
                break;
        }
        
        $this->response->body(View::Factory('admin/auth', array(
                'page_title' => 'Authentication',
                'auth_status' => $auth_status,
            )));
    }
}
?>

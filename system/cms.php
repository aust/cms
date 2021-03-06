<?php if (!defined('INDIRECT')) die();

class CMS
{
    protected static $base_url = '/';
    protected static $initialized = false;
    protected static $modules = array();
    protected static $models = array();
    
    public static function base_url()
    {
        return CMS::$base_url;
    }
    
    public static function Init($parameters = array())
    {
        if (!CMS::$initialized)
        {
            // set base_url if needed
            if (key_exists('base_url', $parameters) &&
                    isset($parameters['base_url']))
            {
                CMS::$base_url = rtrim($parameters['base_url'], '/') . '/';
            }
            
            // Load system extensions
            require_once SYSPATH . 'utilities' . EXT;
            require_once SYSPATH . 'request' . EXT;
            require_once SYSPATH . 'response' . EXT;
            require_once SYSPATH . 'route' . EXT;
            require_once SYSPATH . 'controller' . EXT;
            require_once SYSPATH . 'database' . EXT;
            require_once SYSPATH . 'view' . EXT;
            require_once SYSPATH . 'url' . EXT;
            require_once SYSPATH . 'auth' . EXT;
            require_once SYSPATH . 'model' . EXT;

            Request::Init();
            Response::Init();

            // If the URI starts with index.EXT, we'll remove that and send
            // the user to the correct page. This happens in cases where
            // mod_rewrite sends a user to /index.php/home instead of /home
            if (0 === strpos(Request::Initial()->uri(), CMS::$base_url
                    . 'index' . EXT))
            {
                $new_uri = ltrim(substr(Request::Initial()->uri(),
                               strlen(CMS::$base_url . 'index' . EXT)), '/');
                
                if (strlen($new_uri) > 0)
                    Request::Initial()->Redirect(CMS::$base_url . $new_uri);
                else
                    Request::Initial()->Redirect(rtrim(CMS::$base_url, '/'));
            }
            
            // Initialize database
            Database::Factory(require(APPPATH . 'config/database' . EXT));
            
            CMS::$initialized = true;
        }
    }
    
    public static function Modules($modules = array())
    {
        CMS::$modules = $modules;
        
        foreach ($modules as $module_name => $module_path)
        {
            require_once $module_path . DIRECTORY_SEPARATOR
                    . $module_name . EXT;
        }
    }
    
    public static function Models($models = array())
    {
        CMS::$models = $models;
        
        foreach ($models as $model_path)
        {
            require_once $model_path . EXT;
        }
    }
}

?>

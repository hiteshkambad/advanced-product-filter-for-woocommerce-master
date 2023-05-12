<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

final class APFFW_PN_WP_CRON {

    public $actions = array();
    public $cron_key = null;

    public function __construct($key)
    {
        $this->cron_key = $key;
        $this->actions = get_option($this->cron_key, array());
    }

    public function process()
    {
        if (!empty($this->actions))
        {
            $now = time();
            foreach ($this->actions as $action_hook => $event)
            {
                if ($event['next'] <= $now)
                {
                    $event['next'] = $now + $event['recurrence'];
                    $this->actions[$action_hook] = $event;
                    $this->update();
                    do_action($action_hook);
                }
            }
        }
    }

    public function attach($hook, $start_time, $recurrence)
    {
        $next = $start_time + $recurrence;
        $this->actions[$hook] = array(
            'start_time' => $start_time,
            'next' => $next,
            'recurrence' => $recurrence
        );
        $this->update();
    }

    public function is_attached($hook, $recurrence = 0)
    {
        if (isset($this->actions[$hook]) AND $recurrence !== 0)
        {
            if ((int) $this->actions[$hook]['recurrence'] !== $recurrence)
            {         
                return false;
            }
        }
        return isset($this->actions[$hook]);
    }

    public function remove($hook)
    {
        unset($this->actions[$hook]);
        $this->update();
    }

    public function update()
    {
        update_option($this->cron_key, $this->actions);
    }

}

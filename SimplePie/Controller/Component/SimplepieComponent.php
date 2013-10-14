<?php
/**
 * SimplepieComponent
 *
 * PHP version 5
 *
 * @author   wangshijun <wangshijun2010@gmail.com>
 * @copyright   (c) 2011-2013 wangshijun <wangshijun2010@gmail.com>
 * @package Component
 * @subpackage  HomeComponent
 */
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class SimplepieComponent extends Component {

    private $cache;

    public function __construct() {
        $this->cache = CACHE . 'rss' . DS;
    }

    public function feed($feedUrl, $options=array()) {
        $options = array_merge(array(
            'start' => 0,
            'length' => 30,
            'cache' => true,
            'fields' => array(
                'id',
                'date',
                'title',
                'permalink',
                'description',
                'content',
                'categories',
                'authors',
            )
        ), $options);

        $cacheKey = md5($feedUrl);
        if ($options['cache']) {
            $items = Cache::read($cacheKey);
            if ($items !== false) {
                return $items;
            }
        }

        // make the cache dir if it doesn't exist
        if (!file_exists($this->cache)) {
            $folder = new Folder($this->cache, true);
        }

        // setup SimplePie
        $feed = new SimplePie();
        $feed->set_feed_url($feedUrl);
        $feed->set_cache_location($this->cache);

        // retrieve the feed
        $feed->init();

        // get the feed items
        $items = $feed->get_items($options['start'], $options['length']);

        if ($options['cache']) {
            $cache = array();
            foreach ($items as $item) {
                $holder = array();
                foreach ($options['fields'] as $field) {
                    $holder[$field] = $item->{"get_$field"}();
                }
                $cache[] = $holder;
            }

            Cache::write($cacheKey, $cache);
            return $cache;
        }

        // return
        return ($items) ? $items : array();
    }
}

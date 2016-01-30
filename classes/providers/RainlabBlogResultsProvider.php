<?php
namespace OFFLINE\SiteSearch\Classes\Providers;

use Config;
use Illuminate\Database\Eloquent\Collection;
use RainLab\Blog\Models\Post;
use System\Classes\PluginManager;

/**
 * Searches the contents generated by the
 * Rainlab.Blog plugin
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
class RainlabBlogResultsProvider extends ResultsProvider
{
    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->blogInstalledAndEnabled()) {
            return $this;
        }

        foreach ($this->posts() as $post) {
            // Make this result more relevant, if the query is found in the title
            $relevance = stripos($post->title, $this->query) === false ? 1 : 2;

            $this->addResult($post->title, $post->summary, $this->getUrl($post), $relevance);
        }

        return $this;
    }

    /**
     * Get all posts with matching title or content.
     *
     * @return Collection
     */
    protected function posts()
    {
        return Post::isPublished()
                   ->where('title', 'like', "%{$this->query}%")
                   ->orWhere('content', 'like', "%{$this->query}%")
                   ->orWhere('excerpt', 'like', "%{$this->query}%")
                   ->get();
    }

    /**
     * Checks if the RainLab.Blog Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function blogInstalledAndEnabled()
    {
        return PluginManager::instance()->hasPlugin('RainLab.Blog')
        && Config::get('offline.sitesearch::providers.rainlab_blog.enabled', true);
    }

    /**
     * Genreates the url to a blog post.
     *
     * @param $post
     *
     * @return string
     */
    protected function getUrl($post)
    {
        $url = trim(Config::get('offline.sitesearch::providers.rainlab_blog.posturl', '/blog/post'), '/');

        return implode('/', [$url, $post->slug]);
    }

    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return Config::get('offline.sitesearch::providers.rainlab_blog.label', 'Blog');
    }
}

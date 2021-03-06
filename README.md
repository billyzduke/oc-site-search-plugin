# SiteSearch Plugin for October CMS

This plugin adds global search capabilities to October CMS.

## Available languages

* English
* German
* Czech
* Russian

You can translate all contents into your own language.

## Currently supported content types

* RainLab.Pages (**NEW!** RainLab.Translate compatible)
* RainLab.Blog
* [RadiantWeb.ProBlog](https://octobercms.com/plugin/radiantweb-problog) (**NEW!** RainLab.Translate compatible)
* [Arrizalamin.Portfolio](http://octobercms.com/plugin/arrizalamin-portfolio) (**NEW!** RainLab.Translate compatible)
* Native CMS pages (experimental)


Support for more plugins is added upon request.

**You can easily extend this plugin to search your custom plugin's contents as well.
See the documentation for further information.**


## Components

### searchResults

Place this component on your page to display search results. 

#### Usage example

Create a search form that sends a query to your search page:

##### Search form

```html
<form action="{{ '/search' | app }}" method="get">
    <input name="q" type="text" placeholder="What are you looking for?" autocomplete="off">
    <button type="submit"></button>
</form>
```

**Important**: Use the `q` parameter to send the user's query.

##### Search results

Create a page to display your search results. Add the `searchResults` component to it.
Use the `searchResults.query` parameter to display the user's search query.

```html
title = "Search results"
url = "/search"
...

[searchResults]
resultsPerPage = 10
showProviderBadge = 1
noResultsMessage = "Your search did not return any results."
visitPageMessage = "Visit page"
==
<h2>Search results for {{ searchResults.query }}</h2>

{% component 'searchResults' %}
```

##### Example css to style the component

```css
.ss-result {
    margin-bottom: 2em;
}
.ss-result__title {
    font-weight: bold;
    margin-bottom: .5em;
}
.ss-result__badge {
    font-size: .7em;
    padding: .2em .5em;
    border-radius: 4px;
    margin-left: .75em;
    background: #eee;
    display: inline-block;
}
.ss-result__text {
    margin-bottom: .5em;
}
.ss-result__url {
}
```

#### Properties

The following properties are available to change the component's behaviour.

##### resultsPerPage

How many results to display on one page.

##### showProviderBadge

The search works by querying multiple providers (Pages, Blog, or other). If this option is enabled
each search result is marked with a badge to show which provider returned the result.

This is useful if your site has many different entities (ex. teams, employees, pages, blog entries).

##### noResultsMessage

This message is shown if there are no results returned.

##### visitPageMessage

A link is placed below each search result. Use this property to change that link's text.

## Add support for custom plugin contents

To return search results for you own custom plugin, register an event listener for the `offline.sitesearch.query` 
event in your plugin's boot method.

Return an array containing a `provider` string and `results` array. Each result must provide at least a `title` key.  

### Example to search for custom `documents`

```php
public function boot()
{
    Event::listen('offline.sitesearch.query', function ($query) {
    
        // Search your plugin's contents
        $documents = YourCustomDocumentModel::where('title', 'like', "%${query}%")
                                            ->orWhere('content', 'like', "%${query}%")
                                            ->get();

        // Now build a results array
        $results = [];
        foreach ($documents as $document) {
            // Make this result more relevant if the query
            // is found in the result's title
            $relevance = stripos($document->title, $query) !== false ? 2 : 1;
        
            $results[] = [
                'title'     => $document->title,
                'text'      => $document->content,
                'url'       => '/documents/' . $document->slug,
                'relevance' => $relevance, // higher relevance results in a higher 
                                           // position in the results listing
            ];
        }

        return [
            'provider' => 'Document', // The badge to display for this result
            'results'  => $results,
        ];
    });
}
```

That's it!

## Settings

You can manage all of this plugin's settings in the October CMS backend.

### Rainlab.Pages

No special configuration is required.

### Rainlab.Blog

Make sure you set the `Url of blog post page` setting to point to the right url. Only specify the fixed part of 
the URL: `/blog/post`. If your posts are located under `/blog/post/:slug` the default value is okay.

### RadiantWeb.ProBlog

Make sure you set the `Url of blog post page` setting to point to the right url. Only specify the fixed part of 
the URL: `/blog`. If your posts are located under `/blog/:category/:slug` the default value is okay.

### ArrizalAmin.Portfolio

Make sure you set the `Url of portfolio detail page` setting to point to the right url. Only specify the fixed part of 
the URL: `/portfolio/project`. If your detail page is located under `/portfolio/project/:slug` the default value is okay.

### CMS pages (experimental)

If you want to provide search results for CMS pages change the `enabled` setting to `On`.

You have to specifically add the component `siteSearchInclude` to every CMS page you want to be searched.
Pages **without** this component will **not** be searched.

Components on CMS pages will **not** be rendered. Use this provider only for simple html pages. All Twig syntax will be stripped out to prevent the leaking of source code to the search results.

CMS pages with dynamic URLs (like `/page/:slug`) won't be linked correctly from the search results listing.

If you have CMS pages with dynamic contents consider writing your own search provider (see `Add support for custom 
plugin contents`)


## Overwrite default markup

To overwrite the default markup copy all files from `plugins/offline/sitesearch/components/searchresults` to 
`themes/your-theme/partials/searchResults` and modify them as needed.

If you gave an alias to the `searchResults` component make sure to put the markup in the appropriate partials directory `themes/your-theme/partials/your-given-alias`.

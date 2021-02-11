## News Module

Handle management of news and events.

### Models

#### Types

Categorization for articles. Each type has options that control what information/inputs appear on articles.

#### Articles

Articles are written in MarkDown and converted to HTML upon viewing. Before conversion, an `App\Modules\News\Events\ArticlePrepareContent` event is triggered. This allows for listeners to parse and alter content as needed. An example may be converting patterns such as `FP#12345` into a link to FootPrints ticket #1234. 

**Note**: Each Article produces a Stemmedtext entry, used for searches. Stemmedtext entries have the same ID as the associated article.

#### Updates

Each article can have multiple updates. For example, an article for a system outage may include updates as the outage is being diagnosed, fixed, and returned to functioning status. Updates are also written in MarkDown and trigger an `App\Modules\News\Events\UpdatePrepareContent` event before being displayed.

### Dependencies

* Users Module
  `App\Modules\Users\Models\User` - Used for creators of articles and updates as well as tagging users on articles.
* Resources Module
  `App\Modules\Resources\Entities\Asset` - This is used for tagging articles with Resources.

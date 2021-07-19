Laravel pagination for models with different page sizes (preview-paginator)
===========================================================================

This package allows you to add a local scope to your Model.  
The Pagination class implements the ```Illuminate\Pagination\AbstractPaginator```
contract and it is similar to the basic simplePaginate method.

Installation
============

    composer require a-dob/preview-paginator

Add PreviewPaginated trait to models
====================================

Add ```Adob\PreviewPaginator\PreviewPaginated``` trait to your model you want to paginate

```
use Illuminate\Database\Eloquent\Model;
use Adob\PreviewPaginator\PreviewPaginated;

class Post extends Model
{
    use PreviewPaginated;

    protected $initialQuantity = 2;
    
    protected $perPage = 5;
```

The ```$initialQuantity``` property sets the number of returned models on the first page by default.

The ```$perPage``` property sets the number of returned models on the remaining pages by default.

Usage
=====

You may paginate Eloquent queries. 
In this example, we will paginate the ```App\Models\User``` model 
and indicate that we plan to display 5 records on first page and 15 records on the remaining pages. 
As you can see, the syntax is nearly identical to paginating query builder results:

```
use App\Models\User;

$users = User::previewPaginate(5, 15);
```

Of course, you may call the previewPaginate method after setting other constraints on the query,
such as where clauses:

```
$users = User::where('votes', '>', 100)->previewPaginate(5, 15);
```

You may also use the previewPaginate method when paginating Eloquent models:

```
$users = User::where('votes', '>', 100)->previewPaginate(5, 15);
```

The first argument of the previewPaginated method specifies the number of returned models on the first page.

The second argument of the previewPaginated method specifies the number of returned models on the remaining pages.

Если в качестве первого аргумента передать null то initial quantity будет определено из указанного свойства модели. 
Если в модели данное свойство не определено initial quantity будет равным  ```5```

                  

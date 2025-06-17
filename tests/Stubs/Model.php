<?php

namespace Novius\ScoutElastic\Test\Stubs;

use Illuminate\Database\Eloquent\SoftDeletes;
use Novius\ScoutElastic\Searchable;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use Searchable;
    use SoftDeletes;
}

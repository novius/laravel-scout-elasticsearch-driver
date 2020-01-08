<?php

namespace Novius\ScoutElastic\Test\Stubs;

use Novius\ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use Searchable, SoftDeletes;
}

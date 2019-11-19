{!! Form::open('',['method' => 'get']) !!}
{!! Form::select('order_field', $options, $selected) !!}
{!! Form::submit('<i class="fas fa-sort-up"></i>', ['name' => 'order_type', 'value' => 'asc', 'title' => 'Orden ascendente']) !!}
{!! Form::submit('<i class="fas fa-sort-down"></i>', ['name' => 'order_type', 'value' => 'desc', 'title' => 'Orden descendente']) !!}
{!! Form::close() !!}
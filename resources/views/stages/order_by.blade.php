{!! Form::open(['method' => 'get', 'id' => 'orderForm', 'class' => 'float-right']) !!}
<div class="form-row">
    <div class="col-auto">
        {!! Form::select('order_field', $options, Request::get('order_field'),['class' => 'form-control'] ) !!}
    </div>
    {!! Form::hidden('order', null, ['id' => 'order']) !!}
    <div class="col-auto">
        {!! Form::button('arrow_drop_down', ['title' => 'Orden descendente','class' => 'material-icons form-control border-0', 'onclick' => '$("#order").val("desc");$("#orderForm").submit()']) !!}
    </div>
    <div class="col-auto">
        {!! Form::button('arrow_drop_up', ['title' => 'Orden ascendente','class' => 'material-icons form-control border-0', 'onclick' => '$("#order").val("asc");$("#orderForm").submit()']) !!}
    </div>
</div>
{!! Form::close() !!}
<style>
#orderForm button{
    padding: 0!important;
    margin: 0!important;
}
</style>
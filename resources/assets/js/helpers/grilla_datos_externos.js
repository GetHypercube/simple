
var grid_eliminar = "<input type='checkbox' onclick='selectToDelete(this)' />\
<button type='button' class='btn btn-outline-secondary eliminar' onclick='deleteRow(this)'>\
<i class='material-icons'>delete</i></button>";
var grillas_datatable = {};
var grilla_datos_externos_validar = function(grilla_id, url_for_validate){
    let data = [];
    grillas_datatable[grilla_id].table.data().each(function(ele, index){
        let z = ele.length - 1;
        let arr = [];
        for(let i=0;i<z;i++){
            arr.push(ele[i]);
        }
        data.push(arr);
    });
    $.ajax({
        url: url_for_validate,
        cache: false,
        dataType: "json",
        data: data,
        dataType: "json",
        success: function (data) {
        }
    });
}

var grilla_datos_externos_eliminar = function(grilla_id){
    if(!confirm('Va a elimar las filas seleccionadas. ¿Desea continuar?')) return;
    grillas_datatable[grilla_id].table.rows('.to_delete').remove().draw(true);
}

var modal_agregar_a_grilla = function(grilla_id){
    var modal_new_data = $("#modal-body-"+grilla_id, "form").find("input[name*=modal_input]");
    var modal_grande = $("#addToTableModal_"+grilla_id);
    var to_add = [];
    // FIXME: cambiar of por algo mas comopatible
    for(var el of modal_new_data){
        to_add.push(  $(el).val() );
    }
    if(grillas_datatable[grilla_id].eliminable){
        to_add.push(grid_eliminar);
    }

    grillas_datatable[grilla_id].table.row.add( to_add ).draw( true );

    modal_grande.modal("hide");
}

var deleteRow = function(obj){
    if(!confirm('Va a elimar la fila. ¿Desea continuar?')) return;
    var c_tr = $(obj).closest('tr:parent');
    var grilla_id = $(obj).closest('table:parent').data('grilla_id');
    grillas_datatable[grilla_id].table.row(c_tr).remove().draw(true);
}

var selectToDelete = function(obj){
    var cls = 'to_delete';
    var c_tr = $(obj).closest('tr:parent');
    var c_checkbox = $(obj).prev('input');
    var status = c_tr.hasClass(cls);
    c_checkbox.prop('checked', !!! status);
    if( ! status ){
        c_tr.addClass(cls);
    }else{
        c_tr.removeClass(cls);
    }
}

var grilla_populate_objects = function(grilla_id, data){
    // debe coincidir con la cantidad de columnas en la tabla, pero no viene ese campo ya que es un checkbox
    var eliminable = grillas_datatable[grilla_id].eliminable;
    var headers_obj = grillas_datatable[grilla_id].headers

    var headers = headers_obj.map(function(c){return c.data;});

    grillas_datatable[grilla_id].data_type = 'objects';
    if(eliminable){
        grillas_datatable[grilla_id].columns_length--;
    }

    for(var i=0; i<data.length;i++){
        for(var key in data[i]){
            if(data[i].hasOwnProperty(key) && headers.indexOf(key) == -1 ){
                delete data[i][key];
            }
        }
        for (var key in headers) {
            if (! headers.hasOwnProperty(key)) {
                continue;
            }
            if(!(data[i].hasOwnProperty( headers[key] ))){
                // agregamos lo que falta
                data[i][ headers[key] ] = '';
            }
        }

        if(eliminable)
            data[i]['_eliminar_'] = grid_eliminar;
    }

    grillas_datatable[grilla_id].data = data;
    grillas_datatable[grilla_id].table.rows.add( data ).draw( true );
}

var grilla_populate_arrays = function(grilla_id, data){
    // debe coincidir con la cantidad de columnas en la tabla, pero no viene ese campo ya que es un checkbox
    var eliminable = grillas_datatable[grilla_id].eliminable;
    var cols_num = eliminable ? grillas_datatable[grilla_id].columns_length -1 : grillas_datatable[grilla_id].columns_length;
    grillas_datatable[grilla_id].data_type = 'arrays';

    if(eliminable){
        grillas_datatable[grilla_id].columns_length--;
    }

    for(var i=0; i<data.length;i++){
        if(data[i].length + 1 > grillas_datatable[grilla_id].columns_length){
            data[i] = data[i].slice(0, grillas_datatable[grilla_id].columns_length );
        }

        while(data[i].length < cols_num){
            data[i].push('');
        }

        if(eliminable)
            data[i].push(grid_eliminar);

    }

    grillas_datatable[grilla_id].data = data;
    grillas_datatable[grilla_id].table.rows.add( data ).draw( true );

}

var add_tooltips = function(grilla_id){
    var max_cell_length = grillas_datatable[grilla_id].cell_text_max_length;
    var last_column_index = grillas_datatable[grilla_id].columns_length
    $("#grilla-"+grilla_id).find('tr').each(function(index, tr_element){
        if(index < 1) return; // es header
        var self = $(this);
        // this es tr

        $(tr_element).find('td').each(function(index, td_element){
            if(index >= last_column_index)
                return;
            var td_jquery = $(td_element);
            var text = td_jquery.text();
            if(text.length > max_cell_length){
                td_jquery.attr('data-toggle', 'tooltip');
                td_jquery.attr('data-placement', 'top');
                td_jquery.attr('title', text);
                td_jquery.text(text.slice(0, max_cell_length) + '...');
            }
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
}

var init_tables = function(grilla_id, mode, columns, cell_text_max_length, is_array){
    // var mode = "edicion"; ejemplo
    var tr_header_obj = $("#grilla-" + grilla_id + " tr:first");
    var modal_form = $("#addToTableModal_" + grilla_id + " .modal-body", "form");

    var thead_html = "<th scope='col'>{{text}}</th>\n";
    var modal_form_input_html = '<div class="form-group"><label for="_" class="col-form-label">{{text}}:</label><input type="text" class="form-control" name="modal_input" data-col="{{column}}"></div>';
    var modal_form_not_input = '<div class="form-group"><input type="hidden" name="modal_input" data-col=""></div>';
    grillas_datatable[grilla_id].cell_text_max_length = cell_text_max_length;
    grillas_datatable[grilla_id].exportable_columns_indexes = [];
    grillas_datatable[grilla_id].exportable_columns_name = [];
    for(i=0;i<columns.length;i++){
        tr_header_obj.append(thead_html.replace("{{text}}", columns[i].header));
        if( columns[i].is_exportable=="true")
            grillas_datatable[grilla_id].exportable_columns_indexes.push(i)
            grillas_datatable[grilla_id].exportable_columns_name.push(columns[i].header);
        if(columns[i].is_input=="true"){
            modal_form.append(modal_form_input_html.replace("{{text}}", columns[i].modal_add_text).replace("{{column}}", i));
        }else{
            modal_form.append(modal_form_not_input);
        }
    }

    if(grillas_datatable[grilla_id].eliminable)
        tr_header_obj.append(thead_html.replace("{{text}}", "Eliminar"));

    grillas_datatable[grilla_id].headers = [];
    for(var i=0;i<columns.length;i++){
        if(is_array)
            grillas_datatable[grilla_id].headers.push({title: columns[i].header});
        else{
            if(typeof columns[i].object_field_name == 'undefined' || columns[i].object_field_name == null)
                columns[i].object_field_name = columns[i].header;

            grillas_datatable[grilla_id].headers.push({
                data: columns[i].object_field_name,
                title: columns[i].header
            });
        }
    }

    if(grillas_datatable[grilla_id].eliminable){
        if(is_array){
            grillas_datatable[grilla_id].headers.push({title: 'Eliminar'});
        }else{
            grillas_datatable[grilla_id].headers.push({
                title: 'Eliminar', data: '_eliminar_'
            });
        }
    }


    grillas_datatable[grilla_id].table = $("#grilla-"+grilla_id).DataTable({language:
            {"sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No hay registros que mostrar",
                "sInfo": "Mostrando desde _START_ hasta _END_ de _TOTAL_ registros",
                "sInfoEmpty": "No existen registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ líneas)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "paginate": {
                    "previous": "Anterior ",
                    "next": " Siguiente"
                    }
            },
            responsive: true,
            columnDefs: [{
                className: "display"
            }],
            columns: grillas_datatable[grilla_id].headers
    }).draw(true);
    $("#grilla-"+grilla_id).parents("form").on("submit", function(){
        // construir arreglos
        var exportable_columns_indexes = grillas_datatable[grilla_id].exportable_columns_indexes;

        if(grillas_datatable[grilla_id].exportable_columns_indexes.length == 1){
            var index = grillas_datatable[grilla_id].exportable_columns_indexes[0];
            var data = grillas_datatable[grilla_id].table.columns(index).data().toArray();
        }else{
            if(grillas_datatable[grilla_id].data_type == 'arrays'){
                var indexes = grillas_datatable[grilla_id].exportable_columns_indexes;
                var data = [];
                grillas_datatable[grilla_id].table.rows(
                    function(k, value){
                        var dd = [];
                        for(var i in exportable_columns_indexes){
                            dd.push(value[indexes[i]]);
                        }
                        data.push(dd);
                    }
                );
            }else{
                // objetos
                var column_names = grillas_datatable[grilla_id].exportable_columns_name;
                var data = [];
                grillas_datatable[grilla_id].table.rows(
                    function(k, value){
                        var dd = {};
                        for(var i in column_names){
                            dd[col_name] = value[column_names[i]];
                        }
                        data.push(dd);
                    }
                )
            }
        }

        data_str = JSON.stringify(data);
        $("#"+grilla_id).val( data_str );
        // $("#"+grilla_id).val( data_str.substring(1, data_str.length-1) );
    });

    grillas_datatable[grilla_id].table.on( 'draw', function (grilla_id) {
        return function(evt, settings){
            add_tooltips(grilla_id);
        }
    }(grilla_id));
}

var cambiar_estado_entrada = function(obj, pos){
    var columna_entrada = $('input[name="extra[columns]['+pos+'][is_input]"]');
    columna_entrada.val($(obj).prop('checked'));
}

var cambiar_exportable = function(obj, pos){
    var columna_entrada = $('input[name="extra[columns]['+pos+'][is_exportable]"]');
    columna_entrada.val($(obj).prop('checked'));
}

var toggleAgregable = function(obj){
    var v = $(obj).prop("checked");
    $("input[name=\'extra[agregable]\']").val(v);
}

var toggleEliminable = function(obj){
    var v = $(obj).prop("checked");
    $("input[name=\'extra[eliminable]\']").val(v);
}

var toggleValidable = function(obj){
    var v = $(obj).prop("checked");
    if(v){
        $("input[name=\'extra[validable]\']").val("true");
        $("input[name=\'extra[validate_url]\']").show(500);
        $("select[name=\'extra[validate_method]\']").show(500);
    }else{
        $("input[name=\'extra[validable]\']").val("false");
        $("input[name=\'extra[validate_url]\']").hide(500);
        $("select[name=\'extra[validate_method]\']").hide(500);
    }
}

var open_add_modal = function(grilla_id) {
    var modal = $("#addToTableModal_" + grilla_id );
    modal.find(':text').val("");
    modal.modal('show');
}

var reindex_columns = function(table){
    var num = -2; // la primera fila (0) es headers
    table.find("tr").each(
        function(tr_index, tr_ele){
            num++;
            $(this).find("td").each(
                function(td_index, td_ele){
                    $(this).find(":input").each(function(child_index, child_ele){
                        var old_name= $(child_ele).attr("name");
                        if( typeof old_name === "undefined"){
                            // this un elemento que usa name
                            $(this).data("rownum", num);
                            return;
                        }

                        var new_name = old_name.substring(0, old_name.indexOf("[", old_name.indexOf("[") + 1) + 1);
                        new_name += num;
                        new_name += old_name.substring(old_name.indexOf("]", old_name.indexOf("]") + 1) );
                        $(this).attr("name", new_name);
                    })
                }
            )
        }
    )
}


var nombre_columna_acciones = '_acciones_';
var grid_accion_eliminar = "<div style='float: left;'><input type='checkbox' onclick='selectToDelete(event, this)' />\
<button type='button' class='btn btn-outline-secondary btn_grid_action' onclick='deleteRow(event, this)'>\
<i class='material-icons'>delete</i></button></div>";

var grid_accion_editar = "<button style='float:right;' type='button' class='btn btn-outline-secondary btn_grid_action' onclick='edit_row(event, this)'>\
<i class='material-icons'>edit</i></button>";

var grid_acciones = "{{accion_eliminar}}{{accion_editar}}";
var grillas_datatable = {};

var grilla_datos_externos_eliminar = function(grilla_id){
    if(grillas_datatable[grilla_id].table.rows('.to_delete').data().length <= 0){
        return;
    }
    if(!confirm('Va a elimar las filas seleccionadas. ¿Desea continuar?')) return;
    grillas_datatable[grilla_id].table.rows('.to_delete').remove().draw(true);
}

var modal_agregar_a_grilla = function(grilla_id){
    var modal_grande = $('#table_alter_modal_'+grilla_id);
    var tiene_acciones = grillas_datatable[grilla_id].tiene_acciones;
    var is_array = grillas_datatable[grilla_id].is_array;
    var to_add = is_array ? []: {};
    var headers = grillas_datatable[grilla_id].headers

    $('#modal-body-'+grilla_id, 'form').find('.modal_input').each(function(idx, elemento) {
        var el = $(elemento);
        if(is_array){
            to_add.push(el.val());
        }else{
            var header_key = headers[el.data('column')].data;
            if(header_key === undefined)
                header_key = headers[el.data('column')].title;
            to_add[header_key] = el.val();
        }
    });
    
    if(tiene_acciones){
        if(is_array){
            to_add.push( grillas_datatable[grilla_id].grid_acciones);
        }else{
            to_add[nombre_columna_acciones] = grillas_datatable[grilla_id].grid_acciones;
        }
    }
    
    modal_grande.modal("hide");

    grillas_datatable[grilla_id].table.row.add( to_add ).draw( true );
}

var deleteRow = function(evt, obj){
    evt.stopPropagation();
    evt.preventDefault();
    evt.cancelBubble = true;
    if(!confirm('Va a elimar la fila. ¿Desea continuar?')) return;
    var c_tr = $(obj).closest('tr:parent');
    var grilla_id = $(obj).closest('table:parent').data('grilla_id');
    grillas_datatable[grilla_id].table.row(c_tr).remove().draw(true);
    return false;
}

var selectToDelete = function(evt, obj){
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
    evt.stopPropagation();
    evt.cancelBubble = true;
    return false;
}

var grilla_populate_objects = function(grilla_id, data){
    // debe coincidir con la cantidad de columnas en la tabla, pero no viene ese campo ya que es un checkbox
    var tiene_acciones = grillas_datatable[grilla_id].tiene_acciones;
    var headers_obj = grillas_datatable[grilla_id].headers

    var headers = headers_obj.map(function(c){return c.data;});

    if(tiene_acciones){
        grillas_datatable[grilla_id].cantidad_columnas--;
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

        if(tiene_acciones)
            data[i][nombre_columna_acciones] = grillas_datatable[grilla_id].grid_acciones;
    }
    
    grillas_datatable[grilla_id].data = data;
    grillas_datatable[grilla_id].table.rows.add( data ).draw( true );
}

var grilla_populate_arrays = function(grilla_id, data){
    // debe coincidir con la cantidad de columnas en la tabla, pero no viene ese campo ya que es un checkbox
    var tiene_acciones = grillas_datatable[grilla_id].tiene_acciones;
    var cols_num = tiene_acciones ? grillas_datatable[grilla_id].cantidad_columnas -1 : grillas_datatable[grilla_id].cantidad_columnas;

    if(tiene_acciones){
        grillas_datatable[grilla_id].cantidad_columnas--;
    }

    for(var i=0; i<data.length;i++){
        if(data[i].length + 1 > grillas_datatable[grilla_id].cantidad_columnas){
            data[i] = data[i].slice(0, grillas_datatable[grilla_id].cantidad_columnas );
        }

        for(var j=0;j <data[i].length;j++){
            if(data[i][j] == null)
                data[i][j] = '';
        }
        while(data[i].length < cols_num){
            data[i].push('');
        }

        if(tiene_acciones)
            data[i].push(grillas_datatable[grilla_id].grid_acciones);

    }

    grillas_datatable[grilla_id].data = data;
    
    grillas_datatable[grilla_id].table.rows.add( data ).draw( true );

}

var add_tooltips = function(grilla_id){
    var max_cell_length = grillas_datatable[grilla_id].cell_text_max_length;
    var last_column_index = grillas_datatable[grilla_id].cantidad_columnas
    if(grillas_datatable[grilla_id].tiene_acciones)
        --last_column_index;
    $("#grilla-"+grilla_id).find('tr').each(function(index, tr_element){
        if(index < 1) return; // es header
        var self = $(this);
        // this es tr

        $(tr_element).find('td').each(function(index, td_element){
            if(index > last_column_index)
                return;
            var td_jquery = $(td_element);
            var text = td_jquery.text();
            
            td_jquery.attr('title', text);
            if(text.length > max_cell_length){
                td_jquery.attr('data-toggle', 'tooltip');
                td_jquery.attr('data-placement', 'top');
                td_jquery.text(text.slice(0, max_cell_length) + '...');
            }else{
                // al alicarse el tooltip, title vacio y su contenido para a data-original-title
                // y lo necesitamos para editr
                td_jquery.attr('data-original-title', text);
            }
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
}

var init_tables = function(grilla_id, mode, columns, cell_text_max_length, is_array, is_editable, is_eliminable){
    // var mode = "edicion"; ejemplo
    var tr_header_obj = $("#grilla-" + grilla_id + " tr:first");
    var modal_form = $("#table_alter_modal_" + grilla_id + " .modal-body", "form");

    var thead_html = "<th scope='col'>{{text}}</th>\n";
    var modal_form_input_html = '<div class="form-group"><label for="_" class="col-form-label">{{text}}:</label><input type="text" class="form-control modal_input" ' +
                                    ' data-column="{{column}}"></div>';
    var modal_form_not_input = '<input type="hidden" class="modal_input" data-column="{{column}}">';
    grillas_datatable[grilla_id].cell_text_max_length = cell_text_max_length;
    grillas_datatable[grilla_id].is_array = is_array;
    var accion_eliminar = is_eliminable ? grid_accion_eliminar: '';
    var accion_editar = is_editable ? grid_accion_editar: '';
    grillas_datatable[grilla_id].grid_acciones = grid_acciones.replace('{{accion_eliminar}}', accion_eliminar).replace('{{accion_editar}}', accion_editar);

    grillas_datatable[grilla_id].exportable_columns_indexes = [];
    grillas_datatable[grilla_id].exportable_columns_names = [];
    grillas_datatable[grilla_id].headers = [];
    
    for(var i=0;i<columns.length;i++){
        // creamos el arreglo de cabeceras
        if(typeof columns[i].object_field_name == 'undefined' || columns[i].object_field_name == null){
            // Alertar
            columns[i].object_field_name = columns[i].header;
        }
        if(is_array){
            grillas_datatable[grilla_id].headers.push({title: columns[i].header});
        }else{
            grillas_datatable[grilla_id].headers.push({
                data: columns[i].object_field_name,
                title: columns[i].header
            });
        }
        
        tr_header_obj.append(thead_html.replace("{{text}}", columns[i].header));
        if( columns[i].is_exportable=="true"){
            grillas_datatable[grilla_id].exportable_columns_indexes.push(i)
            grillas_datatable[grilla_id].exportable_columns_names.push({title:columns[i].header, data: columns[i].object_field_name});
        }
        
        // creamos el modal para agregar y editar registros
        if( typeof columns[i].modal_add_text == 'undefined' || columns[i].modal_add_text == null)
                columns[i].modal_add_text = columns[i].header;
        
        var new_element;
        if(columns[i].is_input=="true"){
            new_element = modal_form_input_html;
        }else{
            new_element = modal_form_not_input;            
        }
        modal_form.append(
            new_element.replace("{{text}}", columns[i].modal_add_text)
                                 .replace("{{column}}", i)
        );
        
    }
    

    if(grillas_datatable[grilla_id].tiene_acciones)
        tr_header_obj.append(thead_html.replace("{{text}}", "Acciones"));

    if(grillas_datatable[grilla_id].tiene_acciones){
        if(is_array){
            grillas_datatable[grilla_id].headers.push({title: 'Acciones'});
        }else{
            grillas_datatable[grilla_id].headers.push({
                title: 'Acciones', data: nombre_columna_acciones
            });
        }
    }

    $("#table_alter_modal_" + grilla_id).find(".form-control.modal_input").keypress(function(evt){
        // al presionar "enter" se debe "aceptar" el modal
        if ( evt.which == 13 ){
            $(this).next().focus();
            evt.preventDefault();
            $('#modal_accept_button_' + grilla_id).click();
            return false;
        }
    });
    
    
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
            select: true,
            responsive: true,
            columnDefs: [{
                className: "display"
            }],
            columns: grillas_datatable[grilla_id].headers
    }).draw(true);
    
    $("#grilla-"+grilla_id).parents("form").on("submit", function(){
        // construir arreglos
        var data = [];
        var exportable_columns_names = grillas_datatable[grilla_id].exportable_columns_names;
        if(grillas_datatable[grilla_id].exportable_columns_indexes.length <= 0){
            return;
        }else if(grillas_datatable[grilla_id].exportable_columns_indexes.length == 1){
            var index = grillas_datatable[grilla_id].exportable_columns_indexes[0];
            data = grillas_datatable[grilla_id].table.columns(index).data().toArray()[0];
        }else{
            if( grillas_datatable[grilla_id].is_array ){
                var indexes = grillas_datatable[grilla_id].exportable_columns_indexes;
                var data = [];
                grillas_datatable[grilla_id].table.rows(
                    function(k, value){
                        var dd = [];
                        for(var i in indexes){
                            dd.push(value[indexes[i]]);
                        }
                        data.push(dd);
                    }
                );  
            }else{
                // objetos
                grillas_datatable[grilla_id].table.rows(
                    function(row_index, value){
                        var dd = [];
                        for(i=0;i<exportable_columns_names.length;i++){
                            dd.push( value[exportable_columns_names[i].data] );
                        }
                        data.push(dd);
                    }
                );
            }
        }

        
        if( data.length > 0){
            $("#"+grilla_id).val( JSON.stringify(data) );
        }
    });
    
    grillas_datatable[grilla_id].table.on( 'draw', function (grilla_id) {
        return function(evt, settings){
            add_tooltips(grilla_id);
        }
    }(grilla_id));
    
    grillas_datatable[grilla_id].table.on("click", "tbody tr", function (grilla_id) {
        if( ! grillas_datatable[grilla_id].editable) {
            return function(){}
        }
        return function() {
            var j_row = $(this);
            var dt_row = grillas_datatable[grilla_id].table.row( this );
            var modal = $("#table_alter_modal_" + grilla_id );
            var current_values = [];
            var table_selector = 'td';
            
            if(j_row.has('.dataTables_empty').length > 0){
                // Se hizo click en la fila que muestra "no hay registros que mostrar"
                return;
            }

            if(grillas_datatable[grilla_id].tiene_acciones){
                table_selector += ':not(:last-child)';
            }
            
            j_row.children(table_selector).each(function(idx, ele){
                current_values.push($(ele).attr('data-original-title'));
            });
            
            modal.find('input').each(function(idx, ele){
                $(ele).val( current_values[idx] );
            }); 
            
            $('#add_to_table_modal_label_'+grilla_id).text('Editar Registro')
            $('#modal_accept_button_' + grilla_id).prop("onclick", null).off("click");
            $('#modal_accept_button_' + grilla_id).on("click", function(grilla_id, dt_row, modal){
                return function() {
                    modal_modificar_linea( grilla_id, dt_row, modal);
                    modal.modal("hide");
                }
            }(grilla_id, dt_row, modal));
            modal.modal('show');
        }
    } (grilla_id));
    
}

var edit_row = function(evt, obj) {
    evt.stopPropagation();
    evt.preventDefault();
    evt.cancelBubble = true;
    var j_row = $(obj).parents('tr');
    $(obj).parents('tr').first().click()
    return false;
}

var modal_modificar_linea = function( grilla_id, dt_row, modal){
    var table = grillas_datatable[grilla_id].table;
    var updated_date = [];
    modal.find('input').each(function(idx, ele){ 
        updated_date.push( $(ele).val() );
        table.cell(dt_row, idx).data( $(ele).val() );
    });
    
    table.draw(true);
    add_tooltips(grilla_id);
}

var cambiar_estado_entrada = function(obj, pos){
    var columna_entrada = $('input[name="extra[columns]['+pos+'][is_input]"]');
    columna_entrada.val($(obj).prop('checked'));
}

var cambiar_exportable = function(obj, pos){
    var columna_entrada = $('input[name="extra[columns]['+pos+'][is_exportable]"]');
    columna_entrada.val($(obj).prop('checked'));
}

var toggle_checkbox = function(name, obj){
    var v = $(obj).prop("checked");
    $("input[name=\'extra[" + name + "]\']").val(v);
}

var open_add_modal = function(grilla_id) {
    var modal = $("#table_alter_modal_" + grilla_id );
    $('#add_to_table_modal_label_'+grilla_id).text('Nuevo Registro')
    $('#modal_accept_button_' + grilla_id).prop("onclick", null).off("click");
    $('#modal_accept_button_' + grilla_id).on("click", function(grilla_id){
        return function() {
            modal_agregar_a_grilla( grilla_id);
        }
    }(grilla_id));
    
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

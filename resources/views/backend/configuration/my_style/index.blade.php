@extends('layouts.backend')

@section('title', 'Mis Estilos')

@section('content')
    <div class="container-fluid">
        <div class="row mt-3">

            @include('backend.configuration.nav')

            <div class="col-md-9">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{route('backend.configuration.my_style')}}">Configuración </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Mis Estilos</li>
                    </ol>
                </nav>

                <form name="estilo" id="estilo" action="{{route('backend.configuration.my_style.save')}}" method="post">

                    {{csrf_field()}}

                    <h5>Editar información de mis estilos</h5>
                    <h3>Favor seleccionar todos los campos</h3>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <table>
                                    <tr>
                                        <th colspan="4">Iniciar Sesión</th>
                                    </tr>
                                    <tr>
                                        <td><label for="boton_iniciar_sesion">Botón </label></td>
                                        <td><label for="boton_iniciar_sesion_on_mouse">Botón (OnMouse)</label></td>
                                        <td><label for="texto_iniciar_sesion">Texto </label></td>
                                        <td><label for="texto_iniciar_sesion_on_mouse">Texto (OnMouse)</label></td>
                                    </tr>

                                    <?php 
                                    
                                    ###############
                                    #####################
                                    //Imprimiendo color de boton_iniciar_sesion
                                    $boton_iniciar_sesion = substr($data['personalizacion'], 68, -809);
                                   // echo " boton_iniciar_sesion" ."<pre>" . print_r($boton_iniciar_sesion) . "</pre>";
                                    //Fin boton_iniciar_sesion

                                    //Imprimiendo color de texto_iniciar_sesion
                                    $boton_iniciar_sesion_on_mouse = substr($data['personalizacion'], 121, -756);
                                    //echo " boton_iniciar_sesion_on_mouse" ."<pre>" . print_r($boton_iniciar_sesion_on_mouse) . "</pre>";
                                    //Fin boton_iniciar_sesion_on_mouse

                                    //Imprimiendo color de texto_iniciar_sesion
                                    $texto_iniciar_sesion = substr($data['personalizacion'], 205, -672);
                                    //echo " texto_iniciar_sesion" ."<pre>" . print_r($texto_iniciar_sesion) . "</pre>";
                                    //Fin texto_iniciar_sesion

                                    //Imprimiendo color de texto_iniciar_sesion_on_mouse
                                    $texto_iniciar_sesion_on_mouse = substr($data['personalizacion'], 247, -630);
                                    //echo " texto_iniciar_sesion_on_mouse" ."<pre>" . print_r($texto_iniciar_sesion_on_mouse) . "</pre>";
                                    //Fin texto_iniciar_sesion_on_mouse

                                    //Imprimiendo color de tarjeta_header
                                    $tarjeta_header = substr($data['personalizacion'], 267, -610);
                                    //echo " tarjeta_header" ."<pre>" . print_r($tarjeta_header) . "</pre>";
                                    //Fin tarjeta_header

                                    //Imprimiendo color de tarjeta_footer
                                    $tarjeta_footer = substr($data['personalizacion'], 317, -560);
                                    //echo " tarjeta_footer" ."<pre>" . print_r($tarjeta_footer) . "</pre>";
                                    //Fin tarjeta_footer

                                    //Imprimiendo color de texto_tarjeta_header
                                    $texto_tarjeta_header = substr($data['personalizacion'], 375, -502);
                                    //echo " texto_tarjeta_header" ."<pre>" . print_r($texto_tarjeta_header) . "</pre>";
                                    //Fin texto_tarjeta_header

                                    //Imprimiendo color de texto_tarjeta_footer
                                    $texto_tarjeta_footer = substr($data['personalizacion'], 489, -388);
                                    //echo " texto_tarjeta_footer" ."<pre>" . print_r($texto_tarjeta_footer) . "</pre>";
                                    //Fin texto_tarjeta_footer

                                    //Imprimiendo color de tramite_boton1
                                    $tramite_boton1 = substr($data['personalizacion'], 504, -373);
                                    //echo " tramite_boton1" ."<pre>" . print_r($tramite_boton1) . "</pre>";
                                    //Fin tramite_boton1

                                     //Imprimiendo color de texto_tramite_boton1
                                     $texto_tramite_boton1 = substr($data['personalizacion'], 533, -344);
                                     //echo " texto_tramite_boton1" ."<pre>" . print_r($texto_tramite_boton1) . "</pre>";
                                     //Fin texto_tramite_boton1

                                    //Imprimiendo color de tramite_boton2
                                    $tramite_boton2 = substr($data['personalizacion'], 559, -318);
                                    //echo " tramite_boton2" ."<pre>" . print_r($tramite_boton2) . "</pre>";
                                    //Fin tramite_boton2

                                    //Imprimiendo color de tramite_boton2_on_mouse
                                    $tramite_boton2_on_mouse = substr($data['personalizacion'], 581, -296);
                                    //echo " tramite_boton2_on_mouse" ."<pre>" . print_r($tramite_boton2_on_mouse) . "</pre>";
                                    //Fin tramite_boton2_on_mouse
                      
                                    //Imprimiendo color de texto_tramite_boton2
                                    $texto_tramite_boton2 = substr($data['personalizacion'], 615, -262);
                                    //echo " texto_tramite_boton2" ."<pre>" . print_r($texto_tramite_boton2) . "</pre>";
                                    //Fin texto_tramite_boton2

                                    //Imprimiendo color de texto_tramite_boton2_on_mouse
                                    $texto_tramite_boton2_on_mouse = substr($data['personalizacion'], 641, -236);
                                  //  echo " texto_tramite_boton2_on_mouse" ."<pre>" . print_r($texto_tramite_boton2_on_mouse) . "</pre>";
                                    //Fin texto_tramite_boton2_on_mouse


                                    //Imprimiendo color de tramite_boton3
                                    $tramite_boton3 = substr($data['personalizacion'], 663, -214);
                                    //echo " tramite_boton3" ."<pre>" . print_r($tramite_boton3) . "</pre>";
                                    //Fin tramite_boton3

                                    //Imprimiendo color de tramite_boton3_on_mouse 
                                    $tramite_boton3_on_mouse = substr($data['personalizacion'], 772, -105);
                                    //echo " tramite_boton3_on_mouse" ."<pre>" . print_r($tramite_boton3_on_mouse) . "</pre>";
                                    //Fin tramite_boton3_on_mouse

                                    //Imprimiendo color de texto_tramite_boton3
                                    $texto_tramite_boton3 = substr($data['personalizacion'], 798, -79);
                                    //echo " texto_tramite_boton3" ."<pre>" . print_r($texto_tramite_boton3) . "</pre>";
                                    //Fin texto_tramite_boton3 

                                    //Imprimiendo color de texto_tramite_boton3_on_mouse
                                    $texto_tramite_boton3_on_mouse = substr($data['personalizacion'], 820, -57);
                                    //echo " texto_tramite_boton3_on_mouse". "<pre>" . print_r($texto_tramite_boton3_on_mouse) . "</pre>";
                                    //Fin texto_tramite_boton3_on_mouse 

                                    //Imprimiendo color de tramite_linea
                                    $tramite_linea = substr($data['personalizacion'],874, -3);
                                    //echo " tramite_linea". "<pre>" . print_r($tramite_linea) . "</pre>";
                                    //Fin tramite_linea 
                                  
                                    ?>
                                    
                                    <tr>

                                           
                                        <td>
                                        <input name="boton_iniciar_sesion" type="color" id="favcolor" value=" <?php echo htmlspecialchars($boton_iniciar_sesion);?> " list="b1" onchange="cambiarcolor()"/>
                                        <datalist id="b1">
                                        <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        </td>
          
                                        <td>
                                        <input name="boton_iniciar_sesion_on_mouse" type="color" id="favcolor" value=" <?php echo htmlspecialchars($boton_iniciar_sesion_on_mouse);?> " list="b2" onchange="cambiarcolor()" />
                                        <datalist id="b2">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        </td>
                                        <td>
                                        <input name="texto_iniciar_sesion" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_iniciar_sesion); ?>" list="b3" onchange="cambiarcolor()" />
                                        <datalist id="b3">
                                        <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                         
                                        </td>
                                        <td>
                                        <input name="texto_iniciar_sesion_on_mouse" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_iniciar_sesion_on_mouse);?>" list="b4" onchange="cambiarcolor()" />
                                        <datalist id="b4">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                          
                                        </td>
                                    </tr>
                                </table>
                                
                            </div>
                            
                            <div class="form-group">
                                <table>
                                    <tr>
                                        <th colspan="4">Tarjeta Trámites</th>
                                    </tr>
                                    <tr>
                                        <td><label for="tarjeta_header">Color Header </label></td>
                                        <td><label for="tarjeta_footer">Color Pie </label></td>
                                        <td><label for="texto_tarjeta_header">Texto Header </label></td>
                                        <td><label for="texto_tarjeta_footer">Texto Footer</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                        <input name="tarjeta_header" type="color" id="favcolor" value="<?php echo htmlspecialchars($tarjeta_header);?>" list="tt1" onchange="cambiarcolor()" />
                                        <datalist id="tt1">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        </td>
                                        <td>
                                        <input name="tarjeta_footer" type="color" id="favcolor" value="<?php echo htmlspecialchars($tarjeta_footer);?>" list="tt2" onchange="cambiarcolor()" />
                                        <datalist id="tt2">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        </td>
                                        <td>
                                        <input name="texto_tarjeta_header" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tarjeta_header);?>" list="tt3" onchange="cambiarcolor()" />
                                        <datalist id="tt3">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        
                                        </td>
                                        <td>
                                        <input name="texto_tarjeta_footer" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tarjeta_footer);?>" list="tt4" onchange="cambiarcolor()" />
                                        <datalist id="tt4">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        
                                        </td>
                                    </tr>
                                </table>
                            </div>
                         
                            <div class="form-group">
                                <table>
                                    <tr>
                                        <th colspan="4">SECCIÓN TRÁMITES</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4">Botón Izquierdo</th>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><label for="tramite_boton1">Color</label></td>
                                        <td colspan="2"><label for="texto_tramite_boton1">Color Texto</label></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                        <input name="tramite_boton1" type="color" id="favcolor" value="<?php echo htmlspecialchars($tramite_boton1);?>" list="tt5" onchange="cambiarcolor()" />
                                        <datalist id="tt5">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        
                                        </td>
                                        <td colspan="2">
                                        <input name="texto_tramite_boton1" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tramite_boton1);?>" list="tt6" onchange="cambiarcolor()" />
                                        <datalist id="tt6">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Botón Siguiente</th>
                                    </tr>
                                    <tr>
                                        <td><label for="tramite_boton2">Color</label></td>
                                        <td><label for="tramite_boton2_on_mouse">Color (OnMouse)</label></td>
                                        <td><label for="texto_tramite_boton2">Texto </label></td>
                                        <td><label for="texto_tramite_boton2_on_mouse">Texto (OnMouse)</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                        <input name="tramite_boton2" type="color" id="favcolor" value="<?php echo htmlspecialchars($tramite_boton2);?>" list="tt7" onchange="cambiarcolor()" />
                                        <datalist id="tt7">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                        
                                        </td>
                                        <td>
                                        <input name="tramite_boton2_on_mouse" type="color" id="favcolor" value="<?php echo htmlspecialchars($tramite_boton2_on_mouse);?>" list="tt8" onchange="cambiarcolor()" />
                                        <datalist id="tt8">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                          
                                        </td>
                                        <td>
                                        <input name="texto_tramite_boton2" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tramite_boton2); ?>" list="tt9" onchange="cambiarcolor()" />
                                        <datalist id="tt9">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                            
                                        </td>
                                        <td>
                                        <input name="texto_tramite_boton2_on_mouse" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tramite_boton2_on_mouse);?>" list="tt10" onchange="cambiarcolor()" />
                                        <datalist id="tt10">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                           
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4">Botón Volver</th>
                                    </tr>
                                    <tr>
                                        <td><label for="tramite_boton3">Color </label></td>
                                        <td><label for="tramite_boton3_on_mouse">Color (OnMouse)</label></td>
                                        <td><label for="texto_tramite_boton3">Texto </label></td>
                                        <td><label for="texto_tramite_boton3_on_mouse">Texto (OnMouse)</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                        <input name="tramite_boton3" type="color" id="favcolor" value="<?php echo htmlspecialchars($tramite_boton3);?>" list="tt11" onchange="cambiarcolor()" />
                                        <datalist id="tt11">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                           
                                        </td>
                                        <td>
                                        <input name="tramite_boton3_on_mouse" type="color" id="favcolor" value="<?php echo htmlspecialchars($tramite_boton3_on_mouse);?>" list="tt12" onchange="cambiarcolor()" />
                                        <datalist id="tt12">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                            
                                        </td>
                                        <td>
                                        <input name="texto_tramite_boton3" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tramite_boton3);?>" list="tt13" onchange="cambiarcolor()" />
                                        <datalist id="tt13">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                            
                                        </td>
                                        <td>
                                        <input name="texto_tramite_boton3_on_mouse" type="color" id="favcolor" value="<?php echo htmlspecialchars($texto_tramite_boton3_on_mouse);?>" list="tt14" onchange="cambiarcolor()" />
                                        <datalist id="tt14">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        <option value="#EB1414">
                                        <option value="#0054AB">
                                        <option value="#007328">
                                        </datalist>
                                          
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4"><label for="tramite_linea">Color Línea</label></th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                        <input name="tramite_linea" type="color" id="favcolor" value="<?php echo htmlspecialchars($tramite_linea)?>" list="tt15" onchange="cambiarcolor()" />
                                        <datalist id="tt15">
                                         <option value="#FFFFFF">
                                        <option value="#000000">
                                        </datalist>

                                        </td>
                                    </tr>
                                </table>
                            </div>                            

                            <div class="form-group">
                                <label for="activo">Estado</label>
                                <select name="activo" id="activo" class="activo form-control">
                                    <option value="1" seleted >Activo</option>
                                    <option value="0">No Activo</option>
                                </select>
                            </div>
                            Estilos: <br/>{{ $data->estilos}}
                            <label id="demo"></label><br/>
                            <div class="form-actions">
                                <button type="submit" id="submit" class="btn btn-primary" >Guardar</button>
                                <a href="" class="btn btn-light">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
<script>
function cambiarcolor() {
  let color = document.getElementById("favcolor").value;
}
</script>
@endsection
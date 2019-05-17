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

                <form action="{{route('backend.configuration.my_style.save')}}" method="post">

                    {{csrf_field()}}

                    <h5>Editar información de mis estilos</h5>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <!--<div class="form-group">
                                <label for="name">Tipografia</label>
                                <select name="font" id="font" class="form-control">
                                    <option>Roboto</option>
                                    <option>Tahoma</option>
                                    <option>Arial</option>
                                </select>

                                
                            <!--</div>-->
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
                                    <tr>
                                        <td>
                                            <select name="boton_iniciar_sesion" class="boton_iniciar_sesion form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="boton_iniciar_sesion_on_mouse" class="boton_iniciar_sesion_on_mouse form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_iniciar_sesion" class="texto_iniciar_sesion form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_iniciar_sesion_on_mouse" class="texto_iniciar_sesion_on_mouse form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                                <!--<input name="tramite_linea" id="tramite_linea" type="color" class="form-control"
                                       value="#EB1414"><!-- 8px solid #EB1414 -->    
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
                                            <select name="tarjeta_header" class="tarjeta_header form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>  
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="tarjeta_footer" class="tarjeta_footer form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_tarjeta_header" class="texto_tarjeta_header form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>  
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_tarjeta_footer" class="texto_tarjeta_footer form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
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
                                            <select name="tramite_boton1" class="tramite_boton1 form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td colspan="2">
                                            <select name="texto_tramite_boton1" class="texto_tramite_boton1 form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
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
                                            <select name="tramite_boton2" class="tramite_boton2 form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="tramite_boton2_on_mouse" class="tramite_boton2_on_mouse form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_tramite_boton2" class="texto_tramite_boton2 form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_tramite_boton2_on_mouse" class="texto_tramite_boton2_on_mouse form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
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
                                            <select name="tramite_boton3" class="tramite_boton3 form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="tramite_boton3_on_mouse" class="tramite_boton3_on_mouse form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_tramite_boton3" class="texto_tramite_boton3 form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="texto_tramite_boton3_on_mouse" class="texto_tramite_boton3_on_mouse form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Rojo</option>
                                                <option value="#0054AB" style="color:#0054AB">Azul</option>
                                                <option value="#007328" style="color:#007328">Verde</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4"><label for="tramite_linea">Color Línea</label></th>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <select name="tramite_linea" class="tramite_linea form-control">
                                                <option value="#FFFFFF" style="color:#000000">Blanco</option>
                                                <option value="#000000" style="color:#000000">Negro</option>
                                                <option value="#EB1414" style="color:#EB1414">Color</option>
                                                <option value="#0054AB" style="color:#0054AB">Color</option>
                                                <option value="#007328" style="color:#007328">Color</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </div>                            

                            <div class="form-group">
                                <label for="activo">Estado</label>
                                <select name="activo" class="activo form-control">
                                    <option value="1" seleted >Activo</option>
                                    <option value="0">No Activo</option>
                                </select>
                            </div>
                            Estilos: <br/>{{ $data->estilos}}
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href="" class="btn btn-light">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
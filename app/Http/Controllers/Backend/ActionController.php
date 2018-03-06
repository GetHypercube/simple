<?php

namespace App\Http\Controllers\Backend;

use AccionEnviarCorreo;
use AccionWebservice;
use AccionVariable;
use AccionRest;
use AccionSoap;
use AccionCallback;
use AccionNotificaciones;
use AccionIniciarTramite;
use AccionContinuarTramite;
use AccionDescargaDocumento;
use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ActionController extends Controller
{
    public function list($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['proceso'] = $proceso;
        $data['acciones'] = $data['proceso']->Acciones;
        $data['title'] = 'Triggers';

        return view('backend.action.index', $data);
    }

    public function ajax_seleccionar($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['proceso_id'] = $proceso_id;

        return view('backend.action.ajax_seleccionar', $data);
    }

    public function seleccionar_form(Request $request, $proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $request->validate(['tipo' => 'required']);

        $tipo = $request->input('tipo');

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.action.create', [$proceso_id, $tipo])
        ]);
    }

    public function create($proceso_id, $tipo)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        Log::info("Creando formulario para trámite");
        if ($tipo == 'enviar_correo')
            $accion = new AccionEnviarCorreo();
        else if ($tipo == 'webservice')
            $accion = new AccionWebservice();
        else if ($tipo == 'variable')
            $accion = new AccionVariable();
        else if ($tipo == 'rest')
            $accion = new AccionRest();
        else if ($tipo == 'soap')
            $accion = new AccionSoap();
        else if ($tipo == 'callback')
            $accion = new AccionCallback();
        else if ($tipo == 'webhook')
            $accion = new AccionNotificaciones();
        else if ($tipo == 'iniciar_tramite')
            $accion = new AccionIniciarTramite();
        else if ($tipo == 'continuar_tramite')
            $accion = new AccionContinuarTramite();
        else if ($tipo == 'descarga_documento')
            $accion = new AccionDescargaDocumento();

        $data['edit'] = FALSE;
        $data['proceso'] = $proceso;
        $data['tipo'] = $tipo;
        $data['accion'] = $accion;

        Log::info("Creando formulario para trámite, tipo: " . $data['tipo']);

        $data['title'] = 'Crear Acción';

        return view('backend.action.edit', $data);
    }

    public function edit($accion_id)
    {
        Log::info("####En Editar, id: " . $accion_id);

        $accion = Doctrine::getTable('Accion')->find($accion_id);
        if ($accion->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['edit'] = TRUE;
        $data['proceso'] = $accion->Proceso;
        $data['accion'] = $accion;
        $data['title'] = 'Editar Acción';

        return view('backend.action.edit', $data);
    }

    public function edit_form(Request $request, $accion_id = NULL)
    {
        $accion = NULL;
        if ($accion_id) {
            $accion = Doctrine::getTable('Accion')->find($accion_id);
        } else {
            if ($request->input('tipo') == 'enviar_correo')
                $accion = new AccionEnviarCorreo();
            else if ($request->input('tipo') == 'webservice')
                $accion = new AccionWebservice();
            else if ($request->input('tipo') == 'variable')
                $accion = new AccionVariable();
            else if ($request->input('tipo') == 'rest')
                $accion = new AccionRest();
            else if ($request->input('tipo') == 'soap')
                $accion = new AccionSoap();
            else if ($request->input('tipo') == 'callback')
                $accion = new AccionCallback();
            else if ($request->input('tipo') == 'webhook')
                $accion = new AccionNotificaciones();
            else if ($request->input('tipo') == 'iniciar_tramite')
                $accion = new AccionIniciarTramite();
            else if ($request->input('tipo') == 'continuar_tramite')
                $accion = new AccionContinuarTramite();
            else if ($request->input('tipo') == 'descarga_documento')
                $accion = new AccionDescargaDocumento();
            $accion->proceso_id = $request->input('proceso_id');
            $accion->tipo = $request->input('tipo');
        }

        if ($accion->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar esta accion.';
            exit;
        }
        $request->validate(['nombre' => 'required']);

        $accion->validateForm($request);

        if (!$accion_id) {
            $request->validate([
                'proceso_id' => 'required',
                'tipo' => 'required',
            ]);
        }


        $accion->nombre = $request->input('nombre');
        $accion->extra = $request->input('extra', false);
        $accion->save();

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.action.list', [$accion->Proceso->id])
        ]);
    }

    public function eliminar($accion_id)
    {
        $accion = Doctrine::getTable('Accion')->find($accion_id);

        if ($accion->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar esta accion.';
            exit;
        }

        $proceso = $accion->Proceso;
        $fecha = new \DateTime();

        // Auditar
        $fecha = new \DateTime();
        $registro_auditoria = new \AuditoriaOperaciones();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Acción';
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

        //Detalles
        $accion_array['proceso'] = $proceso->toArray(false);
        $accion_array['accion'] = $accion->toArray(false);
        unset($accion_array['accion']['proceso_id']);
        $registro_auditoria->detalles = json_encode($accion_array);
        $registro_auditoria->save();
        $accion->delete();

        return redirect()->route('backend.action.list', [$proceso->id]);
    }

    public function export($accion_id)
    {
        $accion = Doctrine::getTable('Accion')->find($accion_id);

        $json = $accion->exportComplete();

        header("Content-Disposition: attachment; filename=\"" . mb_convert_case(str_replace(' ', '-', $accion->nombre), MB_CASE_LOWER) . ".simple\"");
        header('Content-Type: application/json');
        echo $json;

    }

    public function import(Request $request)
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            $proceso_id = $request->input('proceso_id');

            if ($file_path && $proceso_id) {
                $input = file_get_contents($_FILES['archivo']['tmp_name']);
                $accion = \Accion::importComplete($input, $proceso_id);
                $accion->proceso_id = $proceso_id;
                $accion->save();
            } else {
                die('No se especificó archivo o ID proceso');
            }
        } catch (Exception $ex) {
            die('Código: ' . $ex->getCode() . ' Mensaje: ' . $ex->getMessage());
        }

        return redirect($_SERVER['HTTP_REFERER']);
    }

    public function functions_soap(Request $request)
    {
        $url = $request->input('urlsoap');
        $request->validate(['urlsoap' => 'required']);

        $client = new \SoapClient($url);
        $result['functions'] = $client->__getFunctions();
        $result['types'] = $client->__getTypes();
        $result['caso'] = 1;
        $result['functions'] = str_replace("\\n", " ", $result['functions']);
        $result['functions'] = str_replace("\\r", " ", $result['functions']);
        $result['types'] = str_replace("\\n", " ", $result['types']);
        $result['types'] = str_replace("\\r", " ", $result['types']);
        $result = str_replace("\\n", " ", $result);
        $result = str_replace("\\r", " ", $result);
        $array = json_encode($result);
        print_r($array);
        exit;
    }

    public function upload_file(Request $request)
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            //$name = $_FILES['tmp_name'];
            if ($file_path) {
                $wsdl = file_get_contents($_FILES['archivo']['tmp_name']);
                $xml = new \SimpleXMLElement($wsdl);
                $config['upload_path'] = "uploads/wsdl/";
                $config['file_name'] = $file_path;
                $config['allowed_types'] = "*";
                $config['max_size'] = "50000";
                $config['max_width'] = "2000";
                $config['max_height'] = "2000";
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('archivo')) {
                    $data['uploadError'] = $this->upload->display_errors();
                    echo $this->upload->display_errors();
                    return;
                }
                $data['uploadSuccess'] = $this->upload->data();
                $file_path = str_replace("/", "", $file_path);
                $wsdl_url = "uploads/wsdl/" . $file_path . ".wsdl";
                $client = new \SoapClient($wsdl_url);
                $result['caso'] = 2;

                Log::info("endpoint: " . $this->varDump($xml->getDocNamespaces(true, true)));
                Log::info("certRequest: " . $this->varDump($client->certRequest));
                Log::info("bindingType: " . $this->varDump($client->bindingType));
                Log::info("curl_options: " . $this->varDump($client->curl_options));
                Log::info("forceEndpoint: " . $this->varDump($client->forceEndpoint));

                $result['targetNamespace'] = $xml['targetNamespace'];
                $result['functions'] = $client->__getFunctions();
                $result['types'] = $client->__getTypes();
                $array = json_encode($result);
                print_r($array);
                exit;
            } else {
                die('No hay archivo');
            }
        } catch (Exception $ex) {
            die('Código: ' . $ex->getCode() . ' Mensaje: ' . $ex->getMessage());
        }
        exit;
    }

    public function converter_json(Request $request)
    {
        $array = $request->input('myArrClean');
        $operaciones = $request->input('operaciones');
        // var_dump($array);
        $DataTypesSoap = ["float", "language", "Qname", "boolean", "gDay", "long", "short", "byte", "gMonth", "Name", "date", "gMonthDay", "NCName", "time", "dateTime", "gYear", "negativeInteger", "token", "decimal", "gYearMonth", "NMTOKEN", "unsignedByte", "double", "ID", "NMTOKENS", "unsignedInt", "duration", "IDREFS", "nonNegativeInteger", "unsignedLong", "ENTITIES", "int", "nonPostiveInteger", "unsignedShort", "ENTITY", "integer", "string", "anyURI", "normalizedString"];

        if (empty($array)) {
            $json = '{}';
            print_r($json);
            exit;
        } else {
            for ($i = 1; $i <= count($array); $i += 2) {
                $array2[$array[$i - 1]] = $array[$i];
            }
            foreach ($array2 as $d) {
                $date = $d;
                $clave = in_array($date, $DataTypesSoap);
                if ($clave == FALSE) {
                    foreach ($operaciones as $d) {
                        $clave = in_array($date, $d);
                        if ($clave != FALSE) {
                            if ($d[1] == $date) {
                                if ($d[0] == 'struct') {
                                    $nuevo = $d;
                                    unset($nuevo[0], $nuevo[1]);
                                    $nuevo2 = array_reverse($nuevo);
                                } else {
                                    $nuevo = $d;
                                    $nuevo2 = array_reverse($nuevo);
                                }
                            }
                        }
                    }
                }
            }
            for ($i = 1; $i <= count($nuevo2); $i += 2) {
                $array3[$nuevo2[$i - 1]] = $nuevo2[$i];
            }

            foreach ($array3 as $d) {
                $date2 = $d;
                $clave2 = in_array($date2, $DataTypesSoap);
                if ($clave2 == FALSE) {
                    foreach ($operaciones as $d) {
                        $clave2 = in_array($date2, $d);
                        if ($clave2 != FALSE) {
                            if ($d[1] == $date2) {
                                if ($d[0] == 'struct') {
                                    $nuevo3 = $d;
                                    unset($nuevo3[0], $nuevo3[1]);
                                    $nuevo4 = array_reverse($nuevo3);
                                    $array4 = "";
                                    for ($i = 1; $i <= count($nuevo4); $i += 2) {
                                        $array4[$nuevo4[$i - 1]] = $nuevo4[$i];
                                    }

                                    foreach ($array3 as $key => $val) {
                                        if ($val == $date2) {
                                            $array3[$key] = $array4;
                                        }
                                    }
                                } else {
                                    $nuevo4 = array_reverse($d);
                                    $i = 0;
                                    $array4 = "";
                                    for ($i = 1; $i <= count($nuevo4); $i += 2) {
                                        $array4[$nuevo4[$i - 1]] = $nuevo4[$i];
                                    }
                                    foreach ($array3 as $key => $val) {
                                        if ($val == $date2) {
                                            $array3[$key] = $array4;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $array2[$date] = $array3;
            }
        }
        $json = json_encode($array2);
        print_r($json);
        exit;
    }

    public function getTareasCallback(Request $request)
    {
        Log::info('En getTareasCallback');
        $id_proceso = $request->input('idProceso');
        Log::info('Input: ' . $id_proceso);
        $tareas_con_callback = Doctrine::getTable('Proceso')->findCallbackProceso($id_proceso);

        //Log::info("####tareas_con_callback: ".$this->varDump($tareas_con_callback));
        foreach ($tareas_con_callback as $tarea) {
            Log::info("Id tarea: " . $tarea["id_tarea"]);
            Log::info("Nombre tarea: " . $tarea["nombre"]);
        }

        $json = json_encode($tareas_con_callback);

        $respuesta = "{\"data\": " . $json . "}";

        Log::info("Respuesta json: " . $respuesta);

        echo $respuesta;
        exit;
    }

    public function getTareasProceso(Request $request)
    {
        Log::info('En getTareasProceso');
        $id_proceso = $request->input('idProceso');
        Log::info('Input: ' . $id_proceso);
        $tareas = Doctrine::getTable('Proceso')->findTareasProceso($id_proceso);

        foreach ($tareas as $tarea) {
            Log::info("Id tarea: " . $tarea["id_tarea"]);
            Log::info("Nombre tarea: " . $tarea["nombre"]);
        }

        $json = json_encode($tareas);

        $respuesta = "{\"data\": " . $json . "}";

        Log::info("Respuesta json: " . $respuesta);

        echo $respuesta;
        exit;
    }

    public function getProcesosCuentas(Request $request)
    {
        Log::info('En getProcesosCuentas');
        $id_cuenta = $request->input('idCuenta');
        $id_cuenta_origen = $request->input('idCuentaOrigen');
        $todos = $request->input('todos');
        Log::info('Input cuenta: ' . $id_cuenta);
        Log::info('Input todos: ' . $todos);
        if ($todos) {
            $tramites_disponibles = Doctrine::getTable('Proceso')->findProcesosExpuestos($id_cuenta);
        } else {
            $proceso_cuenta = new \ProcesoCuenta();
            $tramites_disponibles = $proceso_cuenta->findProcesosExpuestosConPermiso($id_cuenta, $id_cuenta_origen);
        }

        $json = json_encode($tramites_disponibles);

        $respuesta = "{\"data\": " . $json . "}";

        Log::info("Respuesta json: " . $respuesta);

        echo $respuesta;
        exit;
    }

    function varDump($data)
    {
        ob_start();
        //var_dump($data);
        print_r($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }
}

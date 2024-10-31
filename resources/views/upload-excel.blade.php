<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h1>Carga y selecciona una pestaña de Excel</h1>
                    </div>
                    <div class="card-body">
                        <!-- Formulario para cargar el archivo Excel -->
                        <form id="upload-form" action="{{ route('uploadExcel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="excel" class="form-label">Sube tu archivo Excel (Solo permite .xlsx):</label>
                                <input type="file" class="form-control" id="excel" name="excel" required>
                                <button type="submit" class="btn btn-primary mt-3">Cargar Excel</button>
                            </div>
                        </form>

                        @if(session('sheets'))
                        <!-- Formulario para seleccionar la pestaña y generar las pruebas -->
                        <form id="select-sheet-form" action="{{ route('generateDusk') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="sheetName" class="form-label">Selecciona una pestaña:</label>
                                <select class="form-control" id="sheetName" name="sheetName">
                                    @foreach(session('sheets') as $sheet)
                                    <option value="{{ $sheet }}">{{ $sheet }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="rows" class="form-label">Especificar filas (ej., "20", "20, 13, 35, 40", "20-34"):</label>
                                <input type="text" class="form-control" id="rows" name="rows" required>
                            </div>
                            <!-- Campo para el nombre del proyecto -->
                            <div class="mb-3">
                                <label for="project_name" class="form-label">Nombre del proyecto:</label>
                                <input type="text" class="form-control" id="project_name" name="project_name" required>
                            </div>
                            <!-- Campo opcional para prompt extra -->
                            <div class="mb-3">
                                <label for="prompt_extra" class="form-label">Prompt Extra (Opcional):</label>
                                <textarea class="form-control" id="prompt_extra" name="prompt_extra" rows="3" placeholder="Ingresa un prompt adicional si lo deseas."></textarea>
                            </div>
                            <!-- Checkbox para utilizar retroalimentación -->
                            <!-- <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="use_feedback" name="use_feedback" value="1">
                                <label class="form-check-label" for="use_feedback">Utilizar retroalimentación de resultados anteriores</label>
                            </div> -->
                            <button type="submit" class="btn btn-success">Generar Pruebas</button>
                        </form>
                        @endif

                        @if(session('responses'))
                        <!-- Mostrar las respuestas generadas -->
                        <div class="alert alert-success mt-4">
                            @foreach(session('responses') as $response)
                            <pre>{{ $response }}</pre>
                            @endforeach
                        </div>
                        @endif

                        @if(session('success'))
                        <!-- Mostrar mensaje de éxito -->
                        <div class="alert alert-success mt-4">
                            {{ session('success') }}
                        </div>
                        @endif

                        @if($errors->any())
                        <!-- Mostrar errores de validación -->
                        <div class="alert alert-danger mt-4">
                            <ul>
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
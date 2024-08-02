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
                        <form id="upload-form" action="{{ route('uploadExcel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="excel" class="form-label">Sube tu archivo Excel (Solo permite .xlsx):</label>
                                <input type="file" class="form-control" id="excel" name="excel" required>
                                <button type="submit" class="btn btn-primary mt-3">Cargar Excel</button>
                            </div>
                        </form>
                        @if(session('sheets'))
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
                            <button type="submit" class="btn btn-success">Generar Pruebas</button>
                        </form>
                        @endif
                        @if(session('responses'))
                        <div class="alert alert-success mt-4">
                            @foreach(session('responses') as $response)
                            <pre>{{ $response }}</pre>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
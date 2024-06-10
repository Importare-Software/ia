@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h1>Genera tu caso de prueba a partir de una imagen</h1>
                    </div>
                    <div class="card-body">
                        <form id="upload-form" action="{{ route('generate-dusk-code') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="image" class="form-label">Sube tu imagen:</label>
                                <input type="file" class="form-control" id="image" name="imagen" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Generar código Dusk</button>
                        </form>
                        @if(session('response'))
                        <div class="alert alert-success mt-4">
                            <pre>{{ session('response') }}</pre>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
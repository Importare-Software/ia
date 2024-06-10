<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Modelo Manual -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-hand-paper fa-3x mb-3"></i> <!-- Icono FontAwesome -->
                    <h5 class="card-title">Modelo Manual</h5>
                    <p class="card-text">Genera código Dusk de manera manual llenando los inputs.</p>
                    <a href="{{ route('manual-input') }}" class="btn btn-primary">Acceder</a>
                </div>
            </div>
        </div>

        <!-- Modelo de Imagen -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-image fa-3x mb-3"></i> <!-- Icono FontAwesome -->
                    <h5 class="card-title">Modelo de Imagen</h5>
                    <p class="card-text">Genera código Dusk de con solo una imagen.</p>
                    <a href="{{ route('test-dusk') }}" class="btn btn-secondary">Acceder</a>
                </div>
            </div>
        </div>

        <!-- Modelo de Excel -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-file-excel fa-3x mb-3"></i> <!-- Icono FontAwesome -->
                    <h5 class="card-title">Modelo de Excel</h5>
                    <p class="card-text">Genera código Dusk cargando un archivo Excel.</p>
                    <a href="{{ route('upload-excel') }}" class="btn btn-success">Acceder</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
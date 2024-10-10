@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container">
        <h1>Configuración del Chatbot</h1>
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('chatbot.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="assistantName">Nombre del Asistente</label>
                <input type="text" class="form-control" id="assistantName" name="assistantName" value="{{ $settings->assistantName }}">
            </div>

            <div class="form-group">
                <label for="instructions">Instrucciones</label>
                <textarea class="form-control" id="instructions" name="instructions">{{ $settings->instructions }}</textarea>
            </div>

            <div class="form-group">
                <label for="chatModel">Modelo de Chat</label>
                <input type="text" class="form-control" id="chatModel" name="chatModel" value="{{ $settings->chatModel }}">
            </div>

            <div class="form-group">
                <label for="similarityThreshold">Umbral de Similitud</label>
                <input type="number" class="form-control" id="similarityThreshold" name="similarityThreshold" step="0.01" value="{{ $settings->similarityThreshold }}">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Configuración</button>
        </form>
    </div>
</div>
@endsection
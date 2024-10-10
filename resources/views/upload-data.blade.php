@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="mt-5 d-flex justify-content-center w-100">
        <div class="card p-4 shadow-sm" style="width: 500px;">
            <h2 class="mb-4">Cargar Documento</h2>
            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="document">Carga un archivo Markdown:</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="document" name="document" required {{ $hasDocument ? 'disabled' : '' }}>
                        <label class="custom-file-label" for="document">Seleccionar archivo</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3" {{ $hasDocument ? 'disabled' : '' }}>Cargar Documento</button>
            </form>
            @if($hasDocument)
            <form action="{{ route('documents.delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger mt-3">Eliminar Documento</button>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Modal de éxito -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Éxito</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Documento cargado y procesado correctamente.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de error -->
<div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Error al cargar el documento. Por favor, inténtelo de nuevo.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = document.getElementById("document").files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });

    @if(session('success'))
    $('#successModal').modal('show');
    @endif

    @if(session('error'))
    $('#errorModal').modal('show');
    @endif
</script>
@endsection
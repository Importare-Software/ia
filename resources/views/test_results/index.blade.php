@extends('layouts.app')

@section('content')
@if(session('success'))
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Éxito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Operación realizada con éxito.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    var myModal = new bootstrap.Modal(document.getElementById('successModal'));
    myModal.show();
</script>
@endif
<div class="d-flex">
    @include('menu')
    <div class="container">
        <h1>Resultados de IA</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Scenario ID</th>
                    <th>Condition</th>
                    <th>Use Case</th>
                    <th>AI Response</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($testResults as $result)
                <tr>
                    <td>{{ $result->scenario_id }}</td>
                    <td>{{ $result->condition }}</td>
                    <td>{{ $result->use_case }}</td>
                    <td>{{ $result->ai_response }}</td>
                    <td>
                        <a href="{{ route('test_results.edit', $result->id) }}" class="btn btn-primary">Calificar y Corregir</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
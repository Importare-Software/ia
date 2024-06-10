@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container">
        <h1>Calificar Respuesta de IA</h1>
        <div>
            <label>Scenario ID:</label>
            <p>{{ $testResult->scenario_id }}</p>
            <label>Condition:</label>
            <p>{{ $testResult->condition }}</p>
            <label>Use Case:</label>
            <p>{{ $testResult->use_case }}</p>
            <label>Execution Detail:</label>
            <p>{{ $testResult->execution_detail }}</p>
            <label>Expected Results:</label>
            <p>{{ $testResult->expected_results }}</p>
            <label>Locators:</label>
            <p>{{ $testResult->locators }}</p>
            <label>Input Data:</label>
            <p>{{ $testResult->input_data }}</p>
            <label>AI Response:</label>
            <p>{{ $testResult->ai_response }}</p>
        </div>
        <form method="POST" action="{{ route('test_results.update', $testResult->id) }}">
            @csrf
            @method('PUT')
            <div class="rating">
                <label for="ai_response_score" class="form-label">Puntuación de la Respuesta</label>
                <div id="star-rating">
                    <span class="fa fa-star" data-rating="1"></span>
                    <span class="fa fa-star" data-rating="2"></span>
                    <span class="fa fa-star" data-rating="3"></span>
                    <span class="fa fa-star" data-rating="4"></span>
                    <span class="fa fa-star" data-rating="5"></span>
                    <input type="hidden" name="ai_response_score" class="rating-value" value="{{ $testResult->ai_response_score }}">
                </div>
            </div>
            <div class="mb-3">
                <label for="ai_response_corrected" class="form-label">Respuesta Corregida</label>
                <textarea class="form-control" id="ai_response_corrected" name="ai_response_corrected" rows="3" required>{{ $testResult->ai_response_corrected }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('#star-rating .fa-star');
        const ratingValue = document.querySelector('.rating-value');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingValue.value = rating;
                stars.forEach(star => {
                    star.style.color = rating >= star.getAttribute('data-rating') ? '#ffc107' : '#e4e5e9';
                });
            });
        });
        // Set initial rating
        const initialRating = ratingValue.value;
        stars.forEach(star => {
            star.style.color = initialRating >= star.getAttribute('data-rating') ? '#ffc107' : '#e4e5e9';
        });
    });
</script>
@endsection
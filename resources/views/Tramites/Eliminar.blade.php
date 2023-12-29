@extends('layouts.plantilla')

@section('content1')

<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    Confirmar Inhabilitacion
                </div>
                <div class="card-body">
                    <p>¿Estás seguro de que deseas Inhabilitar este Tramite?</p>
                    <p><strong>Nombre:</strong> {{ $tramite->name}}</p>
                    

                    <form action="{{ route('Tramites.Eliminando', $tramite) }}" method="POST">
                        @csrf
                        @method('delete')

                        <div class="d-flex flex-row gap-3 ">
                            <button type="submit" class="btn btn-danger  ">Inhabilitar</button>
                            <a href="{{ route('Tramites.Gestion') }}" class="btn btn-secondary">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>




@endsection
@section('scripts')

<script>
    setTimeout(function() {
        var errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 5000);
</script>
@endsection
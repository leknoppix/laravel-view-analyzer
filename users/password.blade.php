@extends('layouts.admin')

@section('title', __('Création du mot de passe'))

@section('content')
    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-logo">
                    <a href="../">
                        <img class="align-content" src="https://lejournaldugers.fr/imgs/admin/jdg.png" alt=""
                            width="72" height="72">
                    </a>
                </div>
                <div class="login-form">
                    <form method="POST" action="{{ route('User.Validate.Account', $token) }}" class="form-horizontal">
                        @csrf
                        <x-hidden name="token" value="{{ $token }}" />
                        @if (count($errors) > 0)
                            @foreach ($errors->all() as $error)
                                <div class="sufee-alert alert with-close alert-danger alert-dismissible fade show">
                                    <span class="badge badge-pill badge-danger">{{ __('Erreur') }}</span>
                                    {{ $error }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                            @endforeach
                        @endif
                        <h1 class="text-center">{{ __('Création du mot de passe') }}</h1>
                        <div class="form-group">
                            <x-input-label for="email" :value="__('Votre adresse email')" />
                            <x-text-input id="email" name="email" value="{{ $email }}" readonly="true"
                                class="form-control" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="new-password" :value="__('Votre mot de passe')" />
                            <x-password-input id="new-password" name="new-password" required autofocus
                                class="form-control" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="new-password-confirm" :value="__('Confirmation du mot de passe')" />
                            <x-password-input id="new-password-confirm" name="new-password-confirm" required
                                class="form-control" />
                        </div>
                        <x-btn-submit value="Création de mon mot de passe" class="btn btn-success btn-flat m-b-30 m-t-30" />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('body')
    bg-dark
@endsection

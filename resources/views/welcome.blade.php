@extends('layouts.app')

@section('content')
    <div class="page-body" style="width: 100%; height: 100vh; background-color: #121212; padding-top: 50px;">
        <div class="container py-5">
            <!-- Card principal -->
            <div class="card" style="background-color: #27242B; border: none; border-radius: 15px;">
                <div class="card-header text-center" style="border-bottom: none;">
                    <div class="card-title mt-4" style="color: #D394FF; font-weight: bolder; font-size: 2.3rem;">
                        Baixe músicas do YouTube!
                    </div>
                    <div class="card-subtitle" style="font-size: 1.1rem; line-height: 1.6; color: #DCDCDC;">
                        <p>
                            <span style="color: #D394FF; font-weight: bold;">Baixe facilmente</span> sua música favorita do YouTube em formato <strong>MP3</strong>. Sem anúncios, sem limites.
                        </p>
                        <p>
                            Basta inserir o URL abaixo e clicar em <span style="color: #D394FF; font-weight: bold;">"Download"</span> para começar!
                        </p>
                        <p>
                            O <span style="color: #D394FF;">Download</span> será realizado na <strong>melhor qualidade</strong> disponível sempre!
                        </p>
                    </div>
                </div>

                <div class="card-body">
                    <form id="youtube-form" action="{{ route('youtube.download') }}" method="POST" class="d-flex justify-content-center align-items-center" style="max-width: 800px; margin: 0 auto; background-color: #121212; padding: 15px; border-radius: 10px;">
                        @csrf
                        <i class="fa-solid fa-link" style="color: #D394FF; font-size: 1.5rem; margin-right: 15px;"></i>
                        <input type="text" id="youtube-url" name="youtube_url" placeholder="Copie e insira o URL do YouTube"
                               style="background-color: #121212; color: #DCDCDC; font-size: 1rem; height: 50px; flex-grow: 1; padding-left: 15px; border-radius: 10px; width: 100%; transition: all 0.3s ease;">
                        <button type="submit" class="btn" style="background-color: #D394FF; color: white; height: 50px; padding: 0 30px; font-size: 1.3rem; border-radius: 10px; margin-left: 15px; cursor: pointer; transition: all 0.3s ease;">
                            Download
                        </button>
                    </form>
                </div>



                <div class="card-body">
                    <div class="card" style="background-color: #27242B; border: none;">
                        <div class="card-body">
                            <div class="row">
                                <!-- Thumbnail -->
                                <div class="col-12 col-md-6 text-center position-relative mb-4">
                                    <div class="position-relative">
                                        <img id="video-thumbnail" src="{{ asset('images/preview.png') }}" alt="Preview" class="img-fluid rounded-3 shadow-lg" style="opacity: 0.75; display: none;">
                                        <div id="thumbnail-placeholder" class="d-flex justify-content-center align-items-center rounded-3 shadow-lg" style="width: 100%; height: 300px; background: linear-gradient(45deg, #121212, #27242B); display: flex;">
                                            <i id="video-icon" class="fas fa-video-slash text-light" style="font-size: 3rem; color: #D394FF!important;"></i>
                                        </div>

                                        <!-- Loading Overlay -->
                                        <div id="loading-overlay" class="d-flex justify-content-center align-items-center position-absolute top-0 left-0 w-100 h-100 bg-dark bg-opacity-50 rounded-3" style="opacity: 0; visibility: hidden;">
                                            <div class="spinner-border text-light" style="width: 3rem; height: 3rem; color: #D394FF!important;" role="status">
                                                <span class="sr-only">Carregando...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informações do Vídeo -->
                                <div class="col-12 col-md-6 text-center position-relative">
                                    <div class="position-relative">
                                        <div id="info-placeholder" class="d-flex justify-content-center align-items-center rounded-3 shadow-lg" style="width: 100%; height: 300px; background: linear-gradient(45deg, #121212, #27242B); display: flex;">
                                            <i class="fas fa-info-circle text-light" style="font-size: 3rem; color: #D394FF!important;"></i>
                                        </div>
                                        <div id="video-info" class="card" style="background-color: #121212; border: none; border-radius: 15px; padding: 20px; display: none;">
                                            <h3 id="video-title" style="color: #D394FF; font-weight: bold; font-size: 1.5rem; margin-bottom: 15px;"></h3>
                                            <p id="video-uploader" style="color: #DCDCDC; font-size: 1.1rem;"></p>
                                            <p id="video-duration" style="color: #DCDCDC;"></p>
                                            <p id="video-view-count" style="color: #DCDCDC;"></p>
                                            <p id="video-upload-date" style="color: #DCDCDC;"></p>
                                            <p id="video-description" style="color: #DCDCDC;"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mensagem de "Sem Informações" -->
                            <div id="no-data-message" class="text-center" style="display: none; color: #D394FF; font-size: 1.2rem; font-weight: bold; margin-top: 30px;">
                                <p>Não há informações disponíveis para exibir no momento.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('youtube-url').addEventListener('input', function () {
            const youtubeUrl = this.value;
            const loadingOverlay = document.getElementById('loading-overlay');
            const thumbnailPlaceholder = document.getElementById('thumbnail-placeholder');
            const videoThumbnail = document.getElementById('video-thumbnail');
            const infoPlaceholder = document.getElementById('info-placeholder');
            const videoInfo = document.getElementById('video-info');
            const noDataMessage = document.getElementById('no-data-message');
            const videoIcon = document.getElementById('video-icon');

            if (youtubeUrl) {
                // Exibe o overlay de carregamento
                loadingOverlay.style.visibility = 'visible';
                loadingOverlay.style.opacity = 1;

                // Faz a requisição para buscar informações do vídeo
                fetch('{{ route('youtube.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ youtube_url: youtubeUrl })
                })
                    .then(response => response.json())
                    .then(data => {
                        // Oculta o overlay de carregamento
                        loadingOverlay.style.visibility = 'hidden';
                        loadingOverlay.style.opacity = 0;

                        if (data.error) {
                            // Reseta para os placeholders e exibe mensagem de erro
                            thumbnailPlaceholder.style.display = 'flex';
                            thumbnailPlaceholder.style.backgroundImage = 'linear-gradient(45deg, #121212, #27242B)';
                            videoThumbnail.style.display = 'none';
                            videoIcon.style.display = 'block'; // Mostra o ícone novamente

                            infoPlaceholder.classList.add('show-placeholder');
                            infoPlaceholder.classList.remove('hide-placeholder');
                            videoInfo.style.display = 'none';
                            noDataMessage.style.display = 'block';
                        } else {
                            // Atualiza a thumbnail no background do placeholder
                            thumbnailPlaceholder.style.display = 'flex';
                            thumbnailPlaceholder.style.backgroundImage = `url(${data.thumbnail})`;
                            thumbnailPlaceholder.style.backgroundSize = 'cover';
                            thumbnailPlaceholder.style.backgroundPosition = 'center';
                            thumbnailPlaceholder.style.backgroundRepeat = 'no-repeat';
                            videoThumbnail.style.display = 'none'; // Remove a exibição do <img> separado
                            videoIcon.style.display = 'none'; // Oculta o ícone

                            // Atualiza as informações do vídeo
                            document.getElementById('video-title').innerText = data.title || 'Título indisponível';
                            document.getElementById('video-uploader').innerText = data.uploader || 'Artista desconhecido';
                            document.getElementById('video-duration').innerText = `Duração: ${data.duration || '--:--'}`;
                            document.getElementById('video-view-count').innerText = `Visualizações: ${data.view_count || '--'}`;
                            document.getElementById('video-upload-date').innerText = `Data de Publicação: ${data.upload_date || '--'}`;
                            document.getElementById('video-description').innerText = data.description || 'Descrição indisponível';

                            infoPlaceholder.classList.add('hide-placeholder');
                            infoPlaceholder.classList.remove('show-placeholder');
                            videoInfo.style.display = 'block';
                            noDataMessage.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        // Oculta o overlay e exibe mensagem de erro
                        loadingOverlay.style.visibility = 'hidden';
                        loadingOverlay.style.opacity = 0;

                        console.error('Error:', error);
                        thumbnailPlaceholder.style.display = 'flex';
                        videoIcon.style.display = 'block'; // Mostra o ícone novamente

                        infoPlaceholder.classList.add('show-placeholder');
                        infoPlaceholder.classList.remove('hide-placeholder');
                        videoInfo.style.display = 'none';
                        noDataMessage.style.display = 'block';
                    });
            } else {
                // Reseta para o estado inicial
                thumbnailPlaceholder.style.display = 'flex';
                thumbnailPlaceholder.style.backgroundImage = 'linear-gradient(45deg, #121212, #27242B)';
                videoThumbnail.style.display = 'none';
                videoIcon.style.display = 'block'; // Mostra o ícone

                infoPlaceholder.classList.add('show-placeholder');
                infoPlaceholder.classList.remove('hide-placeholder');
                videoInfo.style.display = 'none';
                noDataMessage.style.display = 'none';

                loadingOverlay.style.visibility = 'hidden';
                loadingOverlay.style.opacity = 0;

                document.getElementById('video-title').innerText = '';
                document.getElementById('video-uploader').innerText = '';
                document.getElementById('video-duration').innerText = '';
                document.getElementById('video-view-count').innerText = '';
                document.getElementById('video-upload-date').innerText = '';
                document.getElementById('video-description').innerText = '';
            }
        });

        document.getElementById('youtube-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            // Exibir o SweetAlert de carregamento
            Swal.fire({
                title: 'Aguarde...',
                text: 'Estamos processando seu download!',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); // Exibe o carregando
                }
            });

            // Usar AJAX para enviar o formulário sem recarregar a página
            const formData = new FormData(this);

            fetch("{{ route('youtube.download') }}", {
                method: "POST",
                body: formData
            })
                .then(response => response.json()) // A resposta será em JSON
                .then(data => {
                    Swal.close(); // Fechar o SweetAlert de carregamento

                    if (data.success) {
                        // Mostrar o SweetAlert de sucesso e fechar automaticamente após 2 segundos
                        Swal.fire({
                            title: 'Sucesso!',
                            text: data.message, // Mensagem do backend
                            icon: 'success',
                            showConfirmButton: false, // Remove o botão de confirmação
                            timer: 2000, // Tempo para fechar automaticamente (2 segundos)
                            timerProgressBar: true // Mostra a barra de progresso
                        }).then(() => {
                            location.reload(); // Recarrega a página após o alerta fechar
                        });
                    } else {
                        // Caso haja erro, mostrar um SweetAlert de erro
                        Swal.fire({
                            title: 'Erro!',
                            text: data.error || 'Ocorreu um erro inesperado.',
                            icon: 'error',
                            confirmButtonText: 'Tentar novamente'
                        });
                    }
                })
                .catch(error => {
                    Swal.close(); // Fechar o SweetAlert de carregamento
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao processar o download.',
                        icon: 'error',
                        confirmButtonText: 'Tentar novamente'
                    });
                });
        });


    </script>
@endpush

@push('css')
    <style>
        input:focus, input:hover, input:active, button:focus, button:hover, button:active {
            outline: none;
            box-shadow: none;
            background-color: #121212;
            border-color: #D394FF;
        }

        button {
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #D78B42;
            color: #121212;
            transform: translateY(-2px);
        }

        input:focus {
            border-color: #D394FF;
        }

        .spinner-border {
            animation: rotate 1s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .show-placeholder {
            display: flex !important;
        }

        .hide-placeholder {
            display: none !important;
        }
    </style>
@endpush

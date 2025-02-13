/**
 * imoveis-sp-admin.js
 *
 * Script de administração para o plugin Imóveis SP.
 * - Gerencia a galeria de imagens (upload, visualização, remoção).
 * - Obtem coordenadas via CEP usando a API do Google (ou outra de preferência).
 * - Exemplo de uso do localize_script (ImoveisSPAdminVars) para recuperar a API Key do Google.
 *
 * Observação: É necessário que este arquivo seja registrado e enfileirado no plugin
 * (por exemplo, no método carregar_assets_admin do plugin principal).
 */

jQuery(document).ready(function ($) {
    /**
     * -------------------------------------------------------
     * 1. GALERIA DE IMAGENS (MÚLTIPLAS)
     * -------------------------------------------------------
     *
     * - Botão para abrir o Media Uploader do WordPress.
     * - Permite selecionar múltiplas imagens.
     * - Exibe prévia no container #galeria-imovel-container.
     * - Armazena IDs das imagens no campo oculto #galeria_imovel_ids.
     */
  
    let file_frame; // Variável para o Media Uploader do WordPress
  
    // Botão que abre o Media Uploader para adicionar imagens
    $('#btn-adicionar-imagens-galeria').on('click', function (e) {
      e.preventDefault();
  
      // Se já houver um frame aberto, reabrimos ele
      if (file_frame) {
        file_frame.open();
        return;
      }
  
      // Cria o frame
      file_frame = wp.media({
        title: 'Selecione ou carregue imagens para o Imóvel',
        button: {
          text: 'Usar estas imagens'
        },
        multiple: true // Permite múltiplas seleções
      });
  
      // Quando as imagens são selecionadas
      file_frame.on('select', function () {
        let attachments = file_frame.state().get('selection').toJSON();
        let galeriaIdsInput = $('#galeria_imovel_ids');
        let galeriaContainer = $('#galeria-imovel-container');
  
        // Pega os IDs existentes
        let currentVal = galeriaIdsInput.val();
        let currentIds = currentVal ? currentVal.split(',').map(function (id) { return id.trim(); }) : [];
  
        // Para cada imagem selecionada
        attachments.forEach(function (attachment) {
          // Adiciona o ID ao array de IDs, se ainda não estiver lá
          if (!currentIds.includes(attachment.id.toString())) {
            currentIds.push(attachment.id.toString());
          }
  
          // Cria o thumbnail na galeria
          let thumbUrl = attachment.sizes && attachment.sizes.thumbnail
            ? attachment.sizes.thumbnail.url
            : attachment.url;
  
          let thumbHtml = `
            <div class="galeria-imovel-thumb">
              <img src="${thumbUrl}" alt="" />
              <span class="galeria-remove-img" data-attachment-id="${attachment.id}">x</span>
            </div>
          `;
          galeriaContainer.append(thumbHtml);
        });
  
        // Atualiza o campo oculto com os novos IDs
        galeriaIdsInput.val(currentIds.join(','));
      });
  
      // Abre a janela do Media Uploader
      file_frame.open();
    });
  
    // Evento de remoção de imagem da galeria
    // (usa delegation pois os elementos são criados dinamicamente)
    $('#galeria-imovel-container').on('click', '.galeria-remove-img', function (e) {
      e.preventDefault();
  
      let attachmentId = $(this).data('attachment-id').toString();
      let galeriaIdsInput = $('#galeria_imovel_ids');
      let currentVal = galeriaIdsInput.val();
      let currentIds = currentVal ? currentVal.split(',').map(function (id) { return id.trim(); }) : [];
  
      // Remove o ID do array
      let newIds = currentIds.filter(function (id) {
        return id !== attachmentId;
      });
  
      // Atualiza o campo oculto
      galeriaIdsInput.val(newIds.join(','));
  
      // Remove o elemento do DOM
      $(this).parent('.galeria-imovel-thumb').remove();
    });
  
    /**
     * -------------------------------------------------------
     * 2. OBTER COORDENADAS VIA CEP
     * -------------------------------------------------------
     *
     * - Botão #btn-obter-coordenadas-cep
     * - Lê o valor do input #cep_imovel
     * - Faz requisição à API do Google Geocoding (ou outra) para obter lat/long
     * - Preenche #latitude_imovel e #longitude_imovel
     *
     * Necessita da Google API Key definida em ImoveisSPAdminVars.googleApiKey
     * e ativada para o Google Maps Geocoding, se for usar a Google.
     * Caso queira usar outro serviço (ViaCEP + geocode, etc.), adapte conforme a necessidade.
     */
  
    $('#btn-obter-coordenadas-cep').on('click', async function (e) {
      e.preventDefault();
  
      let cep = $('#cep_imovel').val().trim();
      if (!cep) {
        alert('Por favor, insira um CEP antes de buscar as coordenadas.');
        return;
      }
  
      let apiKey = typeof ImoveisSPAdminVars !== 'undefined' ? ImoveisSPAdminVars.googleApiKey : '';
      if (!apiKey) {
        alert('A chave da API do Google não está configurada. Configure em Imóveis SP > Configurações.');
        return;
      }
  
      // Formata o CEP para remover traços e espaços, se houver
      let cepFormatado = cep.replace('-', '').replace(/\s/g, '');
  
      // Monta a URL para o Google Geocoding API
      // Exemplo: https://maps.googleapis.com/maps/api/geocode/json?address=01001000&key=SUA_API_KEY
      let geocodeUrl = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(cepFormatado)}&key=${apiKey}`;
  
      try {
        let response = await fetch(geocodeUrl);
        if (!response.ok) {
          throw new Error(`Erro ao consultar a API do Google. Status: ${response.status}`);
        }
        let data = await response.json();
  
        if (data.status === 'OK' && data.results && data.results.length > 0) {
          // Pega o primeiro resultado
          let location = data.results[0].geometry.location;
          let latitude = location.lat;
          let longitude = location.lng;
  
          // Preenche os campos
          $('#latitude_imovel').val(latitude);
          $('#longitude_imovel').val(longitude);
  
          alert('Coordenadas obtidas com sucesso!');
        } else {
          alert('Não foi possível encontrar coordenadas para este CEP. Verifique se o CEP está correto.');
        }
      } catch (err) {
        console.error(err);
        alert('Ocorreu um erro ao buscar as coordenadas. Verifique o console para detalhes.');
      }
    });
  });
  
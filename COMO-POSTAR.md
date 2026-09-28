# Painel do blog da CellTrack

O painel fica em `seusite.com/admin/`. Você entra com **e-mail e senha**, escreve o post e clica em **Publicar**.
O post é salvo no GitHub e a Netlify atualiza o site sozinha em ~1 minuto.

## Configuração (uma vez só)

O login é feito pela **DecapBridge** (grátis), que também manda os convites por e-mail.

1. Crie uma conta em https://decapbridge.com e clique para adicionar um site.
2. Informe o repositório do GitHub que a Netlify publica, a branch (`main` ou `master`) e o endereço do site.
3. A DecapBridge pede um **token do GitHub** com permissão de leitura e escrita em *Contents* e *Pull requests* nesse repositório. Ele fica guardado na DecapBridge; ninguém mais precisa dele.
4. A DecapBridge mostra um bloco `backend:`. Cole esse bloco no arquivo `admin/config.yml`, no lugar do que está lá, e envie para o GitHub.
5. Na DecapBridge, convide as pessoas pelo e-mail (você, o Victor…). Cada uma recebe o convite e cria a própria senha.

## Escrever um post

1. Abra `seusite.com/admin/` e entre.
2. **Posts do blog → Novo Post**.
3. Preencha **Título**, **Resumo**, **Data**, **Categoria**, **Imagem de capa** (opcional) e o **Texto**.
4. O lado direito mostra a prévia com o visual do site.
5. Deixe **Rascunho** ligado para salvar sem aparecer no site; desligue e clique em **Publicar** para colocar no ar.

Para editar ou excluir, clique no post na lista.

## Cuidados

- Rascunhos não aparecem no blog, mas ficam em arquivos públicos. Não escreva nada confidencial.
- Use imagens com até ~1600px de largura para o site continuar leve.

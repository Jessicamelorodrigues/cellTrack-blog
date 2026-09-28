# Como postar no blog da CellTrack

O painel fica em `admin.html`. Ele abre com uma **tela de login**, e ninguém vê nem mexe em nada sem entrar.
Existem duas formas de entrar:

## 1. Pelo site no ar (GitHub)

Serve para postar de qualquer computador, em `seusite.com/admin.html`.

1. Preencha **Usuário/organização** e **Repositório** do GitHub do site.
2. Cole o **token de acesso**. Ele é a "senha" e só funciona para quem tem permissão de escrita no repositório. Quem confere isso é o próprio GitHub.
   Como criar: GitHub → **Settings → Developer settings → Personal access tokens → Fine-grained tokens → Generate new token** →
   *Only select repositories* (só o repositório do site) → *Permissions → Contents: Read and write* → escolha uma validade.
3. Marque **Manter conectado** só se o computador for seu.
4. Clique em **Entrar**. Ao **Publicar**, o post vai para o repositório e o site atualiza em 1–2 minutos.

Guarde o token como uma senha. Se ele vazar, apague o token no GitHub (na mesma tela onde foi criado) e gere outro.

## 2. Neste computador (pasta do site)

1. Abra `admin.html` desta pasta no **Chrome** ou no **Edge**.
2. Clique em **Abrir pasta do site** e escolha esta pasta (a que tem `index.html` e `posts.js`).
3. Quando o navegador pedir permissão para editar os arquivos, clique em **Permitir**.

Os posts são gravados direto nos arquivos desta pasta (`posts.js` e `blog-images/`). O painel lembra a pasta até você clicar em **Sair**.

## Escrever um post

1. **+ Novo post**
2. Preencha **Título**, **Resumo** (1–2 frases), **Data** e **Categoria**.
3. **Imagem de capa** (opcional): fotos grandes são reduzidas automaticamente.
4. **Texto**: use os botões (negrito, subtítulo, lista, citação, link, imagem).
5. Confira a aba **Prévia**: ela mostra o post exatamente como fica no site.
6. **Publicar**, ou **Salvar rascunho** para deixar escondido.

Para editar, clique no post na lista da esquerda. **Despublicar** tira do ar sem apagar. **Excluir post** apaga.

> Rascunhos não aparecem no blog, mas ficam dentro do arquivo `posts.js`, que é público.
> Não escreva nada confidencial num rascunho.

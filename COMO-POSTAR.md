# Painel do blog da CellTrack

O painel fica em `seusite.com/admin/`. Ele funciona igual ao do Fofoca Real: login por e-mail e senha, e você convida quem mais pode postar.

> O painel precisa de hospedagem com PHP (Hostinger). No GitHub Pages ele não abre.

## Primeira vez (só uma vez)

1. Envie todos os arquivos do site para a hospedagem (em `public_html`, por exemplo).
2. Abra `seusite.com/admin/`: aparece a tela **Criar sua conta**.
3. Preencha nome, e-mail e senha. Você vira a **administradora principal**.

Faça isso logo depois de enviar os arquivos: essa tela some assim que a primeira conta é criada.

## Convidar alguém

**Usuários → Convidar pessoa** → e-mail → **Enviar convite**.
A pessoa recebe um link (válido por 48h) para criar a própria senha. O link também aparece na tela; se o e-mail não chegar, copie e mande por WhatsApp.

- **Administrador**: só posta.
- **Principal**: posta e gerencia usuários.

Alguém esqueceu a senha? Em **Usuários**, clique em **Nova senha** ao lado da pessoa e mande o link.

## Escrever um post

1. **+ Novo post**
2. **Título**, **Resumo** (1–2 frases), **Data**, **Categoria**.
3. **Imagem de capa** (opcional). Fotos grandes são reduzidas automaticamente.
4. **Texto**: use os botões (negrito, subtítulo, lista, citação, link, imagem).
5. Confira a aba **Prévia**.
6. **Publicar**, ou **Salvar rascunho** para deixar escondido.

Na lista de posts: **Editar**, **Ver** e **Excluir**.

## Cuidados

- Ao enviar uma nova versão do site para a hospedagem, **não substitua** `posts.js`, a pasta `blog-images/` nem a pasta `data/`. Elas guardam os posts e os usuários criados pelo painel.
- Rascunhos não aparecem no blog, mas ficam no arquivo `posts.js`, que é público. Não escreva nada confidencial.

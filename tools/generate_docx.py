import docx
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def create_element(name):
    return OxmlElement(name)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def set_cell_shading(cell, color_hex):
    shading = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{color_hex}"/>')
    cell._tc.get_or_add_tcPr().append(shading)

def build_document(output_path):
    doc = docx.Document()

    # Configuração de Margens ABNT (Superior 3cm, Esquerda 3cm, Inferior 2cm, Direita 2cm)
    for section in doc.sections:
        section.top_margin = Cm(3.0)
        section.left_margin = Cm(3.0)
        section.bottom_margin = Cm(2.0)
        section.right_margin = Cm(2.0)
        section.page_width = Cm(21.0)
        section.page_height = Cm(29.7)

    # Estilos Base
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Arial'
    normal_style.font.size = Pt(12)
    normal_style.font.color.rgb = RGBColor(0, 0, 0)

    def add_p(text="", align=WD_ALIGN_PARAGRAPH.JUSTIFY, first_line_indent=1.25, space_after=6, bold=False, italic=False, size=12):
        p = doc.add_paragraph()
        p.alignment = align
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_after = Pt(space_after)
        p.paragraph_format.space_before = Pt(0)
        if first_line_indent > 0:
            p.paragraph_format.first_line_indent = Cm(first_line_indent)
        else:
            p.paragraph_format.first_line_indent = Cm(0)
        
        if text:
            run = p.add_run(text)
            run.font.name = 'Arial'
            run.font.size = Pt(size)
            run.bold = bold
            run.italic = italic
        return p

    def add_heading_1(text):
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_before = Pt(18)
        p.paragraph_format.space_after = Pt(12)
        p.paragraph_format.first_line_indent = Cm(0)
        run = p.add_run(text.upper())
        run.font.name = 'Arial'
        run.font.size = Pt(12)
        run.bold = True
        return p

    def add_heading_2(text):
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_before = Pt(14)
        p.paragraph_format.space_after = Pt(6)
        p.paragraph_format.first_line_indent = Cm(0)
        run = p.add_run(text.upper())
        run.font.name = 'Arial'
        run.font.size = Pt(12)
        run.bold = False
        return p

    def add_heading_3(text):
        p = doc.add_paragraph()
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(6)
        p.paragraph_format.first_line_indent = Cm(0)
        run = p.add_run(text)
        run.font.name = 'Arial'
        run.font.size = Pt(12)
        run.bold = True
        return p

    def add_code_block(code_text):
        tbl = doc.add_table(rows=1, cols=1)
        tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        cell = tbl.cell(0, 0)
        set_cell_shading(cell, "F4F6F8")
        set_cell_margins(cell, top=140, bottom=140, left=200, right=200)
        
        p = cell.paragraphs[0]
        p.paragraph_format.line_spacing = 1.0
        p.paragraph_format.space_before = Pt(4)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.first_line_indent = Cm(0)
        run = p.add_run(code_text)
        run.font.name = 'Consolas'
        run.font.size = Pt(9.5)
        run.font.color.rgb = RGBColor(30, 41, 59)
        
        doc.add_paragraph().paragraph_format.space_after = Pt(4)

    # --- INÍCIO DO TEXTO ABNT ---

    add_heading_1("4 ARQUITETURA DE INFRAESTRUTURA, CONTEINERIZAÇÃO E DEPLOYMENT")

    add_heading_2("4.1 Visão Geral da Infraestrutura de Produção")

    add_p("Com a finalidade de conferir resiliência, reprodutibilidade, segurança e escalabilidade ao Sistema de Gerenciamento de Oficina Mecânica, concebeu-se uma arquitetura baseada no paradigma de conteinerização e integração contínua (CI/CD). A solução foi projetada para mitigar o clássico problema de discrepância de ambientes (\"funciona na máquina do desenvolvedor, mas falha em produção\"), padronizando o ecossistema de execução desde o estágio de desenvolvimento local até a publicação em nuvem em plataforma de Platform as a Service (PaaS).")

    add_p("A infraestrutura é estruturada em três pilares fundamentais:")

    p = add_p(first_line_indent=0)
    r1 = p.add_run("1. Conteinerização da Aplicação: ")
    r1.bold = True
    p.add_run("Construção de uma imagem única, imutável e auto-suficiente que conjuga o runtime PHP 8.4-FPM e o servidor web reverso Nginx sobre uma distribuição enxuta Alpine Linux;")

    p = add_p(first_line_indent=0)
    r2 = p.add_run("2. Orquestração Multi-Serviços Local: ")
    r2.bold = True
    p.add_run("Utilização do Docker Compose para gerenciar localmente o ecossistema composto pela aplicação web, o Sistema Gerenciador de Banco de Dados (SGBD) PostgreSQL 16 e o serviço de memória em cache Redis 7;")

    p = add_p(first_line_indent=0)
    r3 = p.add_run("3. Esteira de Entrega Contínua e Borda de Produção: ")
    r3.bold = True
    p.add_run("Hospedagem em nuvem via Railway, vinculada a repositório Git (fork), com provisionamento de infraestrutura gerenciada, resolução DNS avançada com CNAME Flattening via Cloudflare e terminação criptográfica SSL/TLS automatizada.")

    # Diagrama em bloco
    diagram_text = (
        "       +--------------------------------------------------------------+\n"
        "       |                  CLIENTE / NAVEGADOR WEB                     |\n"
        "       +--------------------------------------------------------------+\n"
        "                                      |\n"
        "                           HTTPS (Porta 443)\n"
        "                                      v\n"
        "       +--------------------------------------------------------------+\n"
        "       |                 CLOUDFLARE (DNS & EDGE PROXY)                |\n"
        "       |  - Resolução CNAME Flattening no domínio raiz (@) e (www)    |\n"
        "       |  - Roteamento transparente para a infraestrutura PaaS        |\n"
        "       +--------------------------------------------------------------+\n"
        "                                      |\n"
        "                           HTTPS (Proxy Reverso)\n"
        "                                      v\n"
        "       +--------------------------------------------------------------+\n"
        "       |                      RAILWAY EDGE PROXY                      |\n"
        "       |  - Terminação SSL/TLS (Let's Encrypt / ACME Challenge)       |\n"
        "       |  - Injeção dinâmica da porta de borda ($PORT)                |\n"
        "       +--------------------------------------------------------------+\n"
        "                                      |\n"
        "                     HTTP / Roteamento Interno ($PORT)\n"
        "                                      v\n"
        "  ==========================================================================\n"
        "  CONTAINER DOCKER: SISTEMA OFICINA (PHP 8.4-FPM + NGINX ALPINE)\n"
        "  --------------------------------------------------------------------------\n"
        "  |  [ Nginx ]                                                             |\n"
        "  |    -> Escuta dinâmica em $PORT, 80 e 8080                              |\n"
        "  |    -> Entrega imediata de arquivos estáticos (/public)                 |\n"
        "  |    -> Encaminhamento FastCGI com cabeçalhos X-Forwarded-*              |\n"
        "  |                                                                        |\n"
        "  |  [ PHP-FPM 8.4 (127.0.0.1:9000) ]                                      |\n"
        "  |    -> Framework Laravel 12                                             |\n"
        "  |    -> TrustProxies ativado (Reconhecimento de tráfego seguro)          |\n"
        "  |    -> OPcache, PDO PgSQL e Phpredis habilitados                        |\n"
        "  ==========================================================================\n"
        "             |                                             |\n"
        "     Conexão TCP (5432)                            Conexão TCP (6379)\n"
        "             v                                             v\n"
        "  +----------------------+                      +----------------------+\n"
        "  |    POSTGRESQL 16     |                      |       REDIS 7        |\n"
        "  | (Persistência / SGBD)|                      | (Cache / Sessão / SLA|\n"
        "  +----------------------+                      +----------------------+"
    )
    add_code_block(diagram_text)

    p_legenda = add_p("Figura 1 – Diagrama da Arquitetura de Infraestrutura e Roteamento de Borda. Fonte: Elaborado pelo autor (2026).", align=WD_ALIGN_PARAGRAPH.CENTER, first_line_indent=0, size=10, italic=True)

    add_heading_2("4.2 Conteinerização com Docker e Construção em Múltiplos Estágios (Multi-Stage Build)")

    add_p("Para otimizar o tempo de build, reduzir drasticamente a superfície de ataque e minimizar o tamanho final da imagem em disco, adotou-se o padrão Multi-Stage Build no Dockerfile. Esse processo segmenta a esteira de montagem em três estágios especializados e estanques:")

    add_heading_3("4.2.1 Estágio 1: Compilação de Ativos Frontend (node-builder)")
    add_p("O primeiro estágio utiliza a imagem node:18-slim com o objetivo exclusivo de preparar os recursos de interface. Os arquivos de manifesto (package.json e package-lock.json) são isolados em camada para aproveitamento de cache. Em seguida, os módulos necessários são instalados e o empacotador Vite executa a minificação e a compilação dos arquivos JavaScript e CSS para o diretório /build/public/build. As ferramentas do Node.js são descartadas após a conclusão, não compondo a imagem final.")

    add_heading_3("4.2.2 Estágio 2: Resolução de Dependências Backend (composer-builder)")
    add_p("O segundo estágio emprega a imagem oficial composer:2. Realiza-se a instalação das dependências PHP em modo estrito de produção através do comando:")
    add_code_block("composer install --no-dev --no-interaction --no-scripts --optimize-autoloader --prefer-dist --ignore-platform-reqs")
    add_p("Nessa fase, o carregamento dinâmico (autoloader) é otimizado e compilado com a diretiva --ignore-platform-reqs, complementada pela desativação explícita de checagem de plataforma no composer.json (\"platform-check\": false), prevenindo bloqueios de execução gerados por divergências entre o ambiente de construção e o de execução.")

    add_heading_3("4.2.3 Estágio 3: Imagem Definitiva de Produção (production)")
    add_p("A imagem executável final utiliza como base php:8.4-fpm-alpine. A seleção da variante Alpine Linux garante um consumo mínimo de recursos computacionais e memória RAM.")
    add_p("Neste estágio:")
    add_p("• Compilam-se as extensões nativas essenciais do PHP: pdo_pgsql, pgsql, gd, bcmath, opcache, zip, intl e mbstring;", first_line_indent=0)
    add_p("• Instala-se o cliente oficial postgresql-client e as bibliotecas de desenvolvimento libpq-dev;", first_line_indent=0)
    add_p("• Compila-se a extensão redis via repositório PECL utilizando pacotes virtuais temporários (.build-deps), os quais são expurgados na mesma camada para preservação de espaço;", first_line_indent=0)
    add_p("• Instala-se o servidor web Nginx no mesmo container, permitindo um encapsulamento completo do servidor de aplicação e do servidor web em uma unidade funcional coesa;", first_line_indent=0)
    add_p("• Copiam-se unicamente os artefatos compilados dos estágios anteriores (diretórios vendor/ e public/build/), além do código-fonte do sistema;", first_line_indent=0)
    add_p("• Configuram-se as permissões de acesso do usuário de sistema www-data sobre os diretórios de gravação (storage/ e bootstrap/cache/).", first_line_indent=0)

    add_heading_2("4.3 Unificação do Sistema Gerenciador de Banco de Dados (PostgreSQL)")
    add_p("Durante a evolução arquitetural do projeto, homologou-se a substituição do SGBD legado (MySQL) pelo PostgreSQL 16. Essa alteração demandou intervenções profundas tanto na camada de infraestrutura quanto na configuração do framework:")
    add_p("1. Camada de Driver PHP: A dependência da extensão pdo_mysql foi descontinuada no Dockerfile em favor das extensões pdo_pgsql e pgsql, garantindo suporte nativo a tipos de dados avançados, transações atômicas robustas e alta integridade referencial;", first_line_indent=0)
    add_p("2. Ambiente Local e Tratamento de Conflito de Volumes: Durante os testes locais com o Docker Compose, observou-se falha crítica de inicialização no container de banco de dados (sistema-oficina-db-1 exited with code 1). O diagnóstico revelou que o volume persistente nomeado (sistema-oficina_db_data) continha arquivos de dados residuais do motor MySQL (ibdata1, mysql.sock). A resolução exigiu o expurgo completo dos volumes legados via 'docker compose down -v', possibilitando que o utilitário initdb do PostgreSQL 16 estruturasse uma base íntegra e vazia;", first_line_indent=0)
    add_p("3. Parametrização do Framework: O arquivo de configuração config/database.php foi ajustado para priorizar a variável unificada DATABASE_URL, permitindo que o Laravel decodifique dinamicamente string de conexão, credenciais, porta e catálogo, viabilizando interoperabilidade imediata com plataformas de nuvem.", first_line_indent=0)

    add_heading_2("4.4 Ciclo de Vida do Container e Automação Operacional (Entrypoint)")
    add_p("Para viabilizar uma inicialização autônoma, resiliente e compatível com as premissas de sistemas nativos para a nuvem (Cloud Native), implementou-se um script customizado de ponto de entrada (docker/entrypoint.sh).")
    add_p("O script desempenha sequencialmente as seguintes operações no momento do boot do container:")
    add_p("1. Adequação Dinâmica de Portas: Avalia se a variável de ambiente $PORT foi injetada pela plataforma de nuvem. Caso positivo, e se divergente das portas convencionais (80 e 8080), aplica uma injeção de diretiva listen no arquivo docker/nginx/default.conf antes de carregar o serviço;", first_line_indent=0)
    add_p("2. Validação Estrutural do Nginx: Executa nginx -t para comprovar a higidez sintática dos arquivos de configuração do servidor web;", first_line_indent=0)
    add_p("3. Inicialização do Nginx em Segundo Plano: O processo do Nginx é despachado como daemon, iniciando a escuta imediata de conexões na borda;", first_line_indent=0)
    add_p("4. Criação de Vínculo de Armazenamento: Executa php artisan storage:link com tratamento tolerante a falhas, assegurando o acesso público a uploads e documentos gerados;", first_line_indent=0)
    add_p("5. Otimização e Cache de Produção: Executa de forma sequencial o pré-carregamento das configurações (config:cache), o mapa estático de rotas (route:cache) e a pré-compilação dos modelos visuais (view:cache), elevando a taxa de vazão de requisições por segundo;", first_line_indent=0)
    add_p("6. Migração Automatizada do Banco de Dados: Executa php artisan migrate --force. Todas as 22 tabelas estruturais e evolutivas do sistema são criadas ou atualizadas de forma autônoma durante a esteira de implantação;", first_line_indent=0)
    add_p("7. Semeadura Idempotente de Administrador: Executa php artisan db:seed --class=UserSeeder --force. Utilizando o método declarativo User::firstOrCreate(), o sistema garante a existência do usuário gestor inicial (admin@oficina.com), sem ocasionar violações de chave primária ou duplicação de registros em reinicializações posteriores;", first_line_indent=0)
    add_p("8. Despacho do Processo Principal: Transfere a execução para o PHP-FPM em primeiro plano (php-fpm -F), garantindo o encadeamento adequado de sinais POSIX (SIGTERM, SIGINT) para encerramento gracioso (graceful shutdown).", first_line_indent=0)

    add_heading_2("4.5 Orquestração de Desenvolvimento com Docker Compose")
    add_p("O ambiente de homologação e testes locais foi consolidado no manifesto declarativo docker-compose.yml. O ambiente é segregado em uma rede isolada do tipo bridge (app-network) e abrange três serviços interdependentes:")
    add_p("• app: Instância do container consolidado da aplicação. Mapeia a porta hospedeira 8000 para a porta interna 80 do Nginx. Possui dependência declarativa com condição de integridade (condition: service_healthy) em relação ao banco e ao cache;", first_line_indent=0)
    add_p("• db: Instância do PostgreSQL 16 Alpine, configurado com volume persistente db_data e sonda de verificação ativa (healthcheck) executando periodicamente 'pg_isready -U postgres -d sistema_oficina';", first_line_indent=0)
    add_p("• redis: Instância do Redis 7 Alpine, associada ao volume redis_data, com sonda periódica de integridade através do comando 'redis-cli ping'.", first_line_indent=0)
    add_p("Essa configuração assegura que a aplicação Laravel só inicie seu bootstrap após a comprovação inconteste de que os serviços de dados e memória volátil estão aptos a aceitar conexões TCP.")

    add_heading_2("4.6 Pipeline de Integração e Deployment Contínuo (CI/CD) no Railway")
    add_p("A publicação em escala do sistema foi viabilizada mediante a plataforma PaaS Railway, estruturada a partir de um modelo de ramificação (fork) mantido no GitHub (MatheusHMMachado/sistema-oficina).")

    add_heading_3("4.6.1 Configuração Declarativa do Orquestrador")
    add_p("Adicionou-se ao repositório o arquivo de governança de implantação railway.toml, explicitando as diretrizes de compilação e verificação de saúde da instância:")
    add_code_block("[build]\nbuilder = \"dockerfile\"\ndockerfilePath = \"Dockerfile\"\n\n[deploy]\nrestartPolicyType = \"on-failure\"\nrestartPolicyMaxRetries = 3\nhealthcheckPath = \"/health\"\nhealthcheckTimeout = 100")

    add_heading_3("4.6.2 Resolução da Sonda de Integridade (Healthcheck)")
    add_p("Originalmente, a sondagem de integridade apontava para a rota raiz (/). No ecossistema Laravel, o acesso desautenticado a / emite um cabeçalho de redirecionamento HTTP 302 Found apontando para /login. Por padrão, o balanceador do Railway requer estritamente códigos da classe 2xx para validar a inicialização. A divergência causava falso positivo de esgotamento de tempo (healthcheck timeout).")
    add_p("Para sanar a incompatibilidade, implementou-se em routes/web.php uma rota pública e leve:")
    add_code_block("Route::get('/health', function () {\n    return response('OK', 200)->header('Content-Type', 'text/plain');\n});")
    add_p("O script de saúde interno (docker/healthcheck.sh) e o orquestrador da nuvem foram repontados para /health, garantindo estabilidade imediata na esteira de rolling update.")

    add_heading_3("4.6.3 Provisionamento e Variáveis de Referência")
    add_p("Os bancos de dados PostgreSQL e Redis de produção foram instanciados como serviços gerenciados no mesmo projeto. A comunicação entre o serviço web e as bases de dados foi parametrizada por Variáveis de Referência do Railway:")
    add_p("• DATABASE_URL: ${{Postgres.DATABASE_URL}}", first_line_indent=0)
    add_p("• REDIS_URL: ${{Redis.REDIS_URL}}", first_line_indent=0)
    add_p("O uso de referências dinâmicas desacopla as credenciais do código-fonte e garante que quaisquer rotações de credenciais ou manutenções nos servidores de banco sejam propagadas de forma instantânea para a aplicação.")

    add_heading_2("4.7 Resolução de Problemas de Rede, Proxies e Ativos Estáticos")
    add_p("Ao longo da transição para a nuvem, três comportamentos anômalos críticos foram identificados, diagnosticados e solucionados:")

    add_heading_3("4.7.1 Falha 502 Bad Gateway (Connection Refused)")
    add_p("Após a primeira implantação, as requisições direcionadas ao domínio público resultavam em erro HTTP 502 Bad Gateway com o registro de log \"error\": \"connection refused\".")
    add_p("• Causa Raiz: O Dockerfile original expunha conjuntamente as portas 80 e 9000 (EXPOSE 80 9000). O orquestrador da nuvem associou automaticamente a porta alvo (Target Port) do roteador de borda à porta 9000. Como a porta 9000 pertence ao processo FastCGI do PHP-FPM (que não processa protocolo HTTP bruto e operava vinculado ao loopback 127.0.0.1), a conexão pública era imediatamente rejeitada. Concomitantemente, o Railway injetava a variável PORT=8080, enquanto o Nginx aguardava conexões na porta 80;", first_line_indent=0)
    add_p("• Correção Definitiva: Expurgou-se a porta 9000 do EXPOSE do Dockerfile, mantendo exclusivamente EXPOSE 80 8080. O arquivo docker/nginx/default.conf foi reconfigurado para escutar simultaneamente nas portas 80 e 8080 (IPv4 e IPv6). Adicionalmente, os registros de acesso e erro do Nginx foram redirecionados para /dev/stdout e /dev/stderr, conferindo observabilidade total em tempo real no console de monitoramento.", first_line_indent=0)

    add_heading_3("4.7.2 Bloqueio de Conteúdo Misto (Mixed Content) em Produção")
    add_p("Com a resolução do roteamento, o sistema tornou-se acessível pela URL de produção, todavia a interface era renderizada sem nenhuma folha de estilo ou formatação visual.")
    add_p("• Causa Raiz: O tráfego do usuário chegava criptografado via HTTPS (porta 443) no balanceador de borda da nuvem, mas era entregue internamente ao container via HTTP desprotegido (porta 8080). Como o framework Laravel desconhecia que operava atrás de um proxy reverso com terminação SSL, a função auxiliar asset('vendor/tailwindcss.js') gerava URLs com o protocolo http://. Os navegadores modernos bloqueiam terminantemente o carregamento de scripts HTTP inseridos em documentos servidos via HTTPS (Mixed Content Blocking);", first_line_indent=0)
    add_p("• Correção Definitiva: No arquivo bootstrap/app.php, habilitou-se o método $middleware->trustProxies(at: '*');, instruindo o framework a confiar nos cabeçalhos X-Forwarded-*. No Nginx, configurou-se o repasse explícito dos parâmetros HTTP_X_FORWARDED_PROTO, HTTP_X_FORWARDED_FOR e HTTP_X_FORWARDED_PORT para o socket FastCGI. No arquivo resources/views/partials/head-assets.blade.php, alterou-se a referência do script estático para o caminho relativo à raiz (/vendor/tailwindcss.js), eliminando qualquer dependência de schema ou prefixo de domínio rígido.", first_line_indent=0)

    add_heading_3("4.7.3 Incompatibilidade Cruzada com Ambiente Local (localhost)")
    add_p("Ao aplicar forçamento irrestrito de HTTPS para sanar o problema de produção, o ambiente local de desenvolvimento deixou de carregar os estilos, exibindo erro de protocolo SSL no navegador.")
    add_p("• Causa Raiz: A rotina URL::forceScheme('https') havia sido vinculada genericamente à checagem app()->environment('production'). Como o ambiente local executava com a variável APP_ENV=production, o container local passou a requisitar ativos via https://localhost:8000. Como não existiam certificados TLS locais configurados no Docker, a requisição falhava;", first_line_indent=0)
    add_p("• Correção Definitiva: Refinou-se a lógica no app/Providers/AppServiceProvider.php, restringindo o forçamento de esquema apenas quando houver evidência real de transporte seguro (str_starts_with(config('app.url'), 'https://') || request()->header('x-forwarded-proto') === 'https' || request()->isSecure()). Simultaneamente, o arquivo docker-compose.yml teve suas variáveis locais reajustadas para APP_ENV=local e APP_DEBUG=true, restaurando a coexistência pacífica entre desenvolvimento e produção.", first_line_indent=0)

    add_heading_2("4.8 Gestão de Domínio Customizado e Resolução DNS (Hostinger e Cloudflare)")
    add_p("A etapa final de publicação consistiu no apontamento de um domínio registrado junto à registradora Hostinger para a aplicação em nuvem.")

    add_heading_3("4.8.1 O Desafio Técnico do Domínio Raiz e Let's Encrypt")
    add_p("Ao cadastrar o domínio personalizado no Railway, a autoridade certificadora (Let's Encrypt) permaneceu em estado de bloqueio com a mensagem: \"Certificate authority is validating challenges\".")
    add_p("O diagnóstico estabeleceu as seguintes causas:")
    add_p("1. Restrição de CNAME no Ápice (Norma RFC 1034/1035): O Railway exige um apontamento CNAME para rotear o tráfego dinâmico de seus clusters. No entanto, os padrões fundamentais da Internet e o painel nativo da Hostinger proíbem a criação de registros CNAME no domínio raiz (@ ou seudominio.com);", first_line_indent=0)
    add_p("2. Conflito de Registros Pré-Existentes: A Hostinger provisiona, por padrão, registros do tipo A e AAAA apontando para páginas de estacionamento da própria empresa. Ao tentar validar a posse do domínio via desafio HTTP-01/DNS-01, os servidores da autoridade certificadora atingiam a infraestrutura da Hostinger em vez do Railway, inviabilizando a emissão da chave criptográfica;", first_line_indent=0)
    add_p("3. Bloqueio de Redirecionamento Interno: A tentativa de contornar a limitação redirecionando o domínio raiz para o subdomínio www via painel da Hostinger foi rejeitada pelo sistema operacional da registradora sob a alegação de auto-referência (\"Não é possível redirecionar seu domínio para ele mesmo\").", first_line_indent=0)

    add_heading_3("4.8.2 Solução Implementada via Cloudflare (CNAME Flattening)")
    add_p("Para suplantar as limitações estruturais da registradora sem onerar o projeto com custos adicionais, delegou-se a zona de DNS autoritativa para o Cloudflare:")
    add_p("1. Delegação de Servidores de Nomes: Os servidores de nomes padrão da Hostinger foram substituídos pelos nameservers atribuídos pelo Cloudflare;", first_line_indent=0)
    add_p("2. Higienização de Registros Conflitantes: Todos os registros legados do tipo A e AAAA vinculados ao host @ foram revogados da tabela DNS;", first_line_indent=0)
    add_p("3. Aplicação de CNAME Flattening: Criou-se um registro CNAME para o host @ e outro para o subdomínio www, ambos apontando para o alvo canônico do Railway. O recurso de CNAME Flattening do Cloudflare sintetiza respostas simulando registros A na raiz, respeitando a norma RFC e viabilizando o CNAME no ápice;", first_line_indent=0)
    add_p("4. Validação do Certificado com Nuvem Cinza (DNS Only): Durante a etapa inicial de emissão do certificado pelo Let's Encrypt, o proxy do Cloudflare foi configurado no modo DNS Only (desativação temporária do proxy reverso intermediário). Isso permitiu que a autoridade certificadora negociasse o desafio ACME diretamente com o container da aplicação no Railway;", first_line_indent=0)
    add_p("5. Atualização da Variável Canônica: Após o status do domínio transicionar para Active com a emissão do certificado TLS v1.3, atualizou-se no painel do Railway a variável de ambiente APP_URL=https://www.seudominio.com.", first_line_indent=0)

    add_heading_2("4.9 Síntese dos Componentes Tecnológicos da Infraestrutura")
    add_p("A Tabela 1 sintetiza as ferramentas, versões e atribuições funcionais que consolidam a infraestrutura desenvolvida.")

    p_tab_titulo = add_p("Tabela 1 – Matriz de Componentes de Infraestrutura e Implantação.", align=WD_ALIGN_PARAGRAPH.CENTER, first_line_indent=0, size=10, bold=True)

    # Tabela ABNT
    table_data = [
        ["Componente", "Versão / Tecnologia", "Finalidade no Projeto"],
        ["Linguagem / Runtime", "PHP 8.4.26 (FPM)", "Execução central da lógica de negócios e APIs do Laravel 12."],
        ["Sistema Operacional", "Alpine Linux 3.20", "Distribuição mínima e segura para compor a imagem base do container."],
        ["Servidor Web", "Nginx 1.30.4", "Servidor web reverso, terminação interna e entrega de ativos estáticos."],
        ["SGBD Principal", "PostgreSQL 16 Alpine", "Armazenamento persistente de dados transacionais com alta integridade."],
        ["Armazenamento em Cache", "Redis 7 Alpine", "Gerenciamento volátil de sessões, cache de aplicação e filas de serviço."],
        ["Frontend Bundler", "Node.js 18 / Vite 4.5", "Compilação e minificação de bibliotecas e ativos de interface."],
        ["Conteinerização", "Docker (Multi-stage)", "Padronização e isolamento da unidade de software em camadas imutáveis."],
        ["Orquestração Local", "Docker Compose v2", "Composição e inicialização simultânea dos serviços de desenvolvimento."],
        ["Hospedagem em Nuvem", "Railway (PaaS)", "Execução contínua em nuvem com zero-downtime e monitoramento."],
        ["Gestão DNS / Borda", "Cloudflare", "CNAME Flattening, resolução de domínios raiz e mitigação de latência."],
        ["Certificação SSL/TLS", "Let's Encrypt (ACME)", "Criptografia ponta a ponta (HTTPS) com renovação automatizada."]
    ]

    tbl = doc.add_table(rows=len(table_data), cols=3)
    tbl.alignment = WD_TABLE_ALIGNMENT.CENTER

    col_widths = [Cm(4.0), Cm(3.8), Cm(7.2)]

    for row_idx, row in enumerate(table_data):
        for col_idx, cell_text in enumerate(row):
            cell = tbl.cell(row_idx, col_idx)
            cell.width = col_widths[col_idx]
            set_cell_margins(cell, top=80, bottom=80, left=100, right=100)
            
            p = cell.paragraphs[0]
            p.paragraph_format.line_spacing = 1.0
            p.paragraph_format.space_before = Pt(2)
            p.paragraph_format.space_after = Pt(2)
            p.paragraph_format.first_line_indent = Cm(0)
            
            if row_idx == 0:
                set_cell_shading(cell, "E2E8F0")
                run = p.add_run(cell_text)
                run.bold = True
                run.font.name = 'Arial'
                run.font.size = Pt(9.5)
                p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            else:
                if row_idx % 2 == 1:
                    set_cell_shading(cell, "F8FAFC")
                run = p.add_run(cell_text)
                run.font.name = 'Arial'
                run.font.size = Pt(9.5)
                if col_idx == 0:
                    run.bold = True
                    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
                elif col_idx == 1:
                    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
                else:
                    p.alignment = WD_ALIGN_PARAGRAPH.LEFT

    p_fonte = add_p("Fonte: Elaborado pelo autor (2026).", align=WD_ALIGN_PARAGRAPH.CENTER, first_line_indent=0, size=10, italic=True)

    doc.save(output_path)
    print(f"Documento criado com sucesso em: {output_path}")

if __name__ == "__main__":
    out_file = r"c:\Users\mathe\OneDrive\Engenharia de Software\8º Período\TCC 2\sistema-oficina\Docs\04-infraestrutura-conteinerizacao-e-deploy.docx"
    build_document(out_file)

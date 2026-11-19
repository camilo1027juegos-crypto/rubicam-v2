/* ============================================================
   i18n.js — Diccionario de traducciones ES / EN / DE
   y motor de idioma. Cargar ANTES de los demás scripts.
   Para añadir un texto: agrega data-i18n="clave" en el HTML
   y la misma "clave" en los tres idiomas de abajo.
   ============================================================ */
const I18N = {
  es:{
    "nav.home":"Inicio","nav.about":"Sobre nosotros","nav.services":"Servicios","nav.news":"Noticias","nav.contact":"Contáctanos","nav.cta":"Iniciar sesión",
    "about.origin":"Nuestro origen","about.level":"Nuestro nivel y certificaciones","about.team":"Nuestro equipo","about.why":"¿Por qué elegirnos?","about.goals":"Objetivos empresariales",
    "home.eyebrow":"Agencia de aduanas · Nivel 1 DIAN","home.title":"Su carga, despachada sin fricción.","home.lede":"Gestionamos importaciones y exportaciones de principio a fin: clasificación arancelaria, trámite ante la DIAN, cumplimiento cambiario y logística, con trazabilidad total.","home.cta1":"Solicitar cotización","home.cta2":"Ver servicios",
    "tile.op":"Operación · IMPO-2024-0192","tile.port":"Puerto","tile.channel":"Canal","tile.green":"Verde","tile.status":"Estado","tile.cleared":"Levante autorizado",
    "trust.title":"Confían en nuestra operación",
    "feat.eyebrow":"Qué hacemos","feat.title":"Una agencia, toda la cadena","feat.lede":"Cubrimos cada etapa del comercio exterior para que usted se concentre en su negocio, no en los trámites.",
    "feat.1.t":"Importación y exportación","feat.1.d":"Trámite completo ante la DIAN, elaboración de declaraciones y acompañamiento en inspección física o documental.",
    "feat.2.t":"Clasificación arancelaria","feat.2.d":"Determinamos la subpartida correcta para evitar sobrecostos, sanciones y demoras en el levante de su mercancía.",
    "feat.3.t":"Cumplimiento cambiario","feat.3.d":"Declaraciones de cambio y reportes ante el Banco de la República, con el respaldo normativo que su operación exige.",
    "more":"Saber más →",
    "stat.1":"Años de experiencia","stat.2":"Levantes sin observación","stat.3":"Países de origen y destino","stat.4":"Tiempo medio de respuesta",
    "why.eyebrow":"Por qué Rubicam","why.title":"Responsabilidad que respalda cada declaración","why.lede":"Como agencia de aduanas autorizada, respondemos técnicamente por cada trámite. Esa responsabilidad se traduce en rigor, no en burocracia.",
    "why.1":"Autorización vigente DIAN y operador de confianza","why.2":"Un coordinador asignado por cuenta, no un call center","why.3":"Trazabilidad en línea del estado de cada operación","why.4":"Pólizas de cumplimiento y manejo de información",
    "why.panel.eyebrow":"Tablero de operación","why.panel.1":"Declaraciones este mes","why.panel.2":"En inspección","why.panel.3":"Levante autorizado","why.panel.4":"SLA cumplido",
    "quote.text":"Pasamos de no saber dónde estaba nuestra carga a tener el estado de cada contenedor en tiempo real. Rubicam nos quitó la incertidumbre de encima.","quote.name":"María Camila Restrepo","quote.role":"Gerente de Importaciones · Textiles del Norte",
    "cta.title":"¿Tiene una carga por nacionalizar?","cta.lede":"Cuéntenos su operación y le enviamos una cotización clara, sin letra menuda, en menos de 24 horas.","cta.btn":"Hablar con un asesor",
    "about.hero.t":"Más de dos décadas moviendo el comercio exterior del país","about.hero.d":"Somos una agencia de aduanas colombiana enfocada en hacer simple lo complejo: convertir la normativa aduanera en operaciones que fluyen.",
    "about.origin.t":"De una oficina en el puerto a aliado estratégico","about.origin.d1":"Rubicam nació en el muelle, resolviendo trámites que otros consideraban imposibles. Con los años convertimos ese conocimiento de calle en procesos, tecnología y un equipo que entiende que detrás de cada declaración hay un negocio esperando.","about.origin.d2":"Hoy acompañamos a importadores y exportadores de todos los tamaños, desde la pyme que hace su primera compra internacional hasta operaciones de gran volumen con regímenes especiales.",
    "about.level.t":"Autorizados, auditados y respaldados","about.level.d":"Operamos bajo las autorizaciones y estándares que exige el comercio exterior colombiano. Nuestras certificaciones no son un adorno: son la garantía de que su mercancía está en manos idóneas.",
    "cert.1":"Autorización DIAN","cert.2":"Operador Económico Autorizado","cert.3":"Gestión de calidad","cert.4":"Comercio seguro",
    "about.team.t":"Personas que responden, no formularios","about.team.d":"Un equipo de agentes de aduana, clasificadores y coordinadores logísticos que conoce su operación por nombre propio.",
    "team.1.n":"Ricardo Bedoya","team.1.r":"Director de operaciones","team.2.n":"Ana Lucía Gómez","team.2.r":"Jefe de clasificación","team.3.n":"Julián Ortiz","team.3.r":"Coordinador logístico","team.4.n":"Carolina Díaz","team.4.r":"Cumplimiento cambiario",
    "about.why.t":"Rigor técnico con trato humano","about.why.1":"Respondemos técnicamente por cada declaración que firmamos.","about.why.2":"Visibilidad en línea: usted siempre sabe en qué canal y etapa está su carga.","about.why.3":"Cotizaciones transparentes, sin cobros sorpresa al final.","about.why.4":"Cobertura en los principales puertos y aeropuertos del país.",
    "about.goals.t":"Hacia dónde vamos","goals.1.t":"Cero demoras evitables","goals.1.d":"Reducir al mínimo los tiempos de levante mediante trámites bien preparados desde el origen.","goals.2.t":"Digitalización total","goals.2.d":"Que cada cliente siga su operación en línea, con documentos y estados en un solo lugar.","goals.3.t":"Comercio sostenible","goals.3.d":"Acompañar a nuestros clientes en prácticas de comercio seguro y responsable.",
    "svc.hero.t":"Todo el comercio exterior, bajo un mismo techo","svc.hero.d":"Desde la clasificación de su producto hasta la entrega final, cubrimos cada eslabón de la cadena aduanera y logística.",
    "svc.import.t":"Importaciones","svc.import.d":"Nacionalización de mercancía, elaboración de la declaración de importación, liquidación de tributos aduaneros y acompañamiento en inspección ante la DIAN.",
    "svc.export.t":"Exportaciones","svc.export.d":"Trámite de la declaración de exportación (DEX), gestión documental y coordinación para que su mercancía salga del país sin contratiempos.",
    "svc.clas.t":"Clasificación arancelaria","svc.clas.d":"Identificación de la subpartida arancelaria correcta, estudios de clasificación y solicitud de resoluciones anticipadas para dar certeza a su operación.",
    "svc.advice.t":"Asesoría en comercio exterior","svc.advice.d":"Acompañamiento en tratados de libre comercio, regímenes especiales, zonas francas y aprovechamiento de preferencias arancelarias.",
    "svc.fx.t":"Cumplimiento cambiario","svc.fx.d":"Elaboración de declaraciones de cambio y reportes ante el Banco de la República, para operaciones de importación, exportación y financiación.",
    "svc.log.t":"Logística y transporte","svc.log.d":"Coordinación de transporte internacional y nacional, almacenamiento, seguros de carga y distribución hasta la puerta de su bodega.",
    "svc.cta.t":"¿No sabe por dónde empezar?","svc.cta.d":"Describa su operación y le decimos exactamente qué necesita y cuánto cuesta.",
    "news.hero.t":"Normativa y comercio exterior, al día","news.hero.d":"Cambios en la regulación aduanera, calendarios y consejos prácticos para que su operación nunca se tome por sorpresa.","news.img":"Imagen del artículo",
    "news.1.tag":"Regulación","news.1.t":"Nuevos controles para la importación de textiles en 2026","news.1.d":"Qué cambia en los requisitos de etiquetado y cómo prepararse para evitar retenciones en puerto.","news.1.m":"12 ene 2026 · 4 min de lectura",
    "news.2.tag":"Guía práctica","news.2.t":"Cómo leer el semáforo de la DIAN sin entrar en pánico","news.2.d":"Rojo, amarillo o verde: qué significa cada canal y qué debe tener listo en cada caso.","news.2.m":"3 dic 2025 · 6 min de lectura",
    "news.3.tag":"Comercio","news.3.t":"Aprovechar los TLC: preferencias que muchos dejan sobre la mesa","news.3.d":"Un certificado de origen bien gestionado puede ahorrarle miles en aranceles. Le explicamos cómo.","news.3.m":"18 nov 2025 · 5 min de lectura",
    "contact.hero.t":"Hablemos de su operación","contact.hero.d":"Déjenos los datos de su carga y un asesor le responde con una cotización clara en menos de 24 horas.",
    "form.name":"Nombre completo","form.company":"Empresa","form.email":"Correo electrónico","form.type":"Tipo de operación","form.type.import":"Importación","form.type.export":"Exportación","form.type.advice":"Asesoría","form.type.other":"Otro","form.msg":"Cuéntenos sobre su carga",
    "form.terms.1":"Acepto los","form.terms.2":"He leído y acepto la","form.terms.3":"y el tratamiento de mis datos","form.submit":"Enviar solicitud","form.note":"Registramos la fecha, hora y versión de las políticas que usted acepta.",
    "contact.addr.t":"Oficina principal","contact.addr.d":"Terminal de carga · Bogotá, Colombia","contact.phone.t":"Teléfono","contact.mail.t":"Correo","contact.hours.t":"Horario","contact.hours.d":"Lun a Vie · 8:00 – 18:00",
    "legal.eyebrow":"Legal","legal.terms":"términos y condiciones","legal.privacy":"política de privacidad","legal.cookies":"política de cookies","legal.refund":"política de reembolso","legal.updated":"Última actualización: enero de 2026",
    "legal.terms.t":"Términos y condiciones","legal.terms.box":"Este es un documento modelo. Debe ser revisado y adaptado por un abogado antes de su publicación, conforme a la legislación colombiana y los servicios reales de Rubicam.",
    "legal.terms.h1":"1. Objeto","legal.terms.p1":"Estos términos regulan el uso del sitio web de Rubicam y la relación entre la agencia y los usuarios que solicitan información o servicios de agenciamiento aduanero.",
    "legal.terms.h2":"2. Servicios","legal.terms.p2":"Rubicam presta servicios de agenciamiento aduanero, asesoría en comercio exterior y logística. El alcance de cada operación se define en la cotización y el contrato correspondiente.",
    "legal.terms.h3":"3. Responsabilidad del usuario","legal.terms.p3":"El usuario se compromete a entregar información veraz y completa sobre su mercancía. La exactitud de los documentos aportados es fundamental para el correcto trámite aduanero.",
    "legal.terms.h4":"4. Propiedad intelectual","legal.terms.p4":"Los contenidos, marcas y diseños de este sitio son propiedad de Rubicam o de sus licenciantes y no pueden reproducirse sin autorización.",
    "legal.terms.h5":"5. Ley aplicable","legal.terms.p5":"Estos términos se rigen por la legislación de la República de Colombia. Cualquier controversia se someterá a los jueces competentes del domicilio de Rubicam.",
    "legal.privacy.t":"Política de privacidad y tratamiento de datos","legal.privacy.box":"Documento modelo conforme a la Ley 1581 de 2012 de Protección de Datos de Colombia. Debe validarse con el responsable de tratamiento y un asesor legal.",
    "legal.privacy.h1":"1. Responsable del tratamiento","legal.privacy.p1":"Rubicam es responsable del tratamiento de los datos personales recolectados a través de este sitio, con la finalidad de atender solicitudes y prestar sus servicios.",
    "legal.privacy.h2":"2. Datos que recolectamos","legal.privacy.p2":"Recolectamos únicamente los datos necesarios: nombre, empresa, correo electrónico y los detalles de la operación que usted decide compartir. No pedimos información que no necesitemos.",
    "legal.privacy.h3":"3. Finalidad","legal.privacy.p3":"Usamos sus datos para responder su solicitud, enviar cotizaciones y, si usted lo autoriza, comunicarle novedades. No los vendemos ni los cedemos a terceros sin su consentimiento.",
    "legal.privacy.h4":"4. Sus derechos","legal.privacy.p4":"Usted puede conocer, actualizar, rectificar y suprimir sus datos, así como revocar la autorización, escribiendo a nuestro correo de contacto.",
    "legal.privacy.h5":"5. Registro del consentimiento","legal.privacy.p5":"Cada vez que usted acepta esta política en un formulario, guardamos la fecha, la hora y la versión del documento aceptado, como constancia del consentimiento otorgado.",
    "legal.cookies.t":"Política de cookies",
    "legal.cookies.h1":"1. Qué son las cookies","legal.cookies.p1":"Las cookies son pequeños archivos que se almacenan en su dispositivo para recordar sus preferencias, como el idioma elegido, y para entender cómo se usa el sitio.",
    "legal.cookies.h2":"2. Cookies que usamos","legal.cookies.l1":"<b>Necesarias:</b> permiten el funcionamiento básico del sitio (por ejemplo, recordar el idioma). No requieren consentimiento.","legal.cookies.l2":"<b>Analíticas:</b> nos ayudan a medir el uso del sitio. Solo se activan si usted lo acepta.",
    "legal.cookies.h3":"3. Consentimiento","legal.cookies.p3":"Al entrar al sitio le mostramos un aviso para que acepte o rechace las cookies no esenciales. Puede cambiar su decisión en cualquier momento borrando las cookies de su navegador.","legal.cookies.box":"Sí, su sitio necesita consentimiento para las cookies analíticas o de marketing. Las puramente técnicas (como recordar el idioma) no lo requieren. El banner de este sitio ya implementa esa distinción.",
    "legal.refund.t":"Política de reembolso","legal.refund.box":"Documento modelo. Ajuste los plazos y condiciones a la realidad de sus servicios antes de publicarlo.",
    "legal.refund.h1":"1. Ámbito","legal.refund.p1":"Esta política aplica a los servicios de asesoría y gestión contratados directamente con Rubicam. Los tributos aduaneros y pagos a terceros (DIAN, transportadores, puertos) se rigen por sus propias reglas.",
    "legal.refund.h2":"2. Solicitudes de reembolso","legal.refund.p2":"Si un servicio no se prestó o se cobró por error, puede solicitar su reembolso dentro de los plazos pactados en el contrato, escribiendo a nuestro correo de contacto.",
    "legal.refund.h3":"3. Tiempos","legal.refund.p3":"Las solicitudes se estudian y responden en un plazo razonable. De ser procedentes, el reembolso se realiza por el mismo medio de pago utilizado.",
    "footer.about":"Agencia de aduanas autorizada. Movemos su comercio exterior con rigor técnico y trazabilidad total.","footer.nav":"Navegación","footer.services":"Servicios","footer.legal":"Legal","footer.rights":"Todos los derechos reservados.","footer.cookieSettings":"Configurar cookies",
    "cookie.title":"Usamos cookies","cookie.text":"Usamos cookies necesarias para que el sitio funcione y, con su permiso, cookies analíticas para mejorarlo. Consulte nuestra","cookie.accept":"Aceptar todas","cookie.reject":"Solo necesarias",
    "_toast.sent":"✓ Solicitud enviada. Le responderemos en menos de 24 horas.","_toast.consent":"Por favor acepte ambas casillas para continuar.","_toast.news":"Artículo de ejemplo — próximamente.","_toast.cookieOk":"Preferencias de cookies guardadas."
  },
  en:{
    "nav.home":"Home","nav.about":"About us","nav.services":"Services","nav.news":"News","nav.contact":"Contact","nav.cta":"Log in",
    "about.origin":"Our origin","about.level":"Our level & certifications","about.team":"Our team","about.why":"Why choose us?","about.goals":"Business objectives",
    "home.eyebrow":"Customs agency · DIAN Level 1","home.title":"Your cargo, cleared without friction.","home.lede":"We handle imports and exports end to end: tariff classification, DIAN clearance, exchange compliance and logistics, with full traceability.","home.cta1":"Request a quote","home.cta2":"View services",
    "tile.op":"Operation · IMPO-2024-0192","tile.port":"Port","tile.channel":"Channel","tile.green":"Green","tile.status":"Status","tile.cleared":"Release authorized",
    "trust.title":"Trusted in their operations",
    "feat.eyebrow":"What we do","feat.title":"One agency, the whole chain","feat.lede":"We cover every stage of foreign trade so you can focus on your business, not on paperwork.",
    "feat.1.t":"Import & export","feat.1.d":"Full DIAN clearance, declaration filing and support during physical or documentary inspection.",
    "feat.2.t":"Tariff classification","feat.2.d":"We determine the correct subheading to avoid overcharges, penalties and delays in releasing your goods.",
    "feat.3.t":"Exchange compliance","feat.3.d":"Exchange declarations and reports to the Banco de la República, with the regulatory backing your operation requires.",
    "more":"Learn more →",
    "stat.1":"Years of experience","stat.2":"Releases with no findings","stat.3":"Origin & destination countries","stat.4":"Average response time",
    "why.eyebrow":"Why Rubicam","why.title":"Accountability behind every declaration","why.lede":"As an authorized customs agency, we are technically liable for every filing. That responsibility means rigor, not bureaucracy.",
    "why.1":"Current DIAN authorization and trusted operator","why.2":"A dedicated coordinator per account, not a call center","why.3":"Online traceability of every operation's status","why.4":"Compliance and data-handling insurance",
    "why.panel.eyebrow":"Operations board","why.panel.1":"Declarations this month","why.panel.2":"In inspection","why.panel.3":"Release authorized","why.panel.4":"SLA met",
    "quote.text":"We went from not knowing where our cargo was to seeing every container's status in real time. Rubicam took the uncertainty off our shoulders.","quote.name":"María Camila Restrepo","quote.role":"Import Manager · Textiles del Norte",
    "cta.title":"Got cargo to clear?","cta.lede":"Tell us about your operation and we'll send a clear quote, no fine print, in under 24 hours.","cta.btn":"Talk to an advisor",
    "about.hero.t":"Over two decades moving the country's foreign trade","about.hero.d":"We are a Colombian customs agency focused on making the complex simple: turning customs regulation into operations that flow.",
    "about.origin.t":"From a dockside office to a strategic ally","about.origin.d1":"Rubicam was born on the docks, solving procedures others deemed impossible. Over the years we turned that street knowledge into processes, technology and a team that knows there's a business waiting behind every declaration.","about.origin.d2":"Today we serve importers and exporters of every size, from the SME making its first international purchase to high-volume operations under special regimes.",
    "about.level.t":"Authorized, audited and backed","about.level.d":"We operate under the authorizations and standards Colombian foreign trade demands. Our certifications aren't decoration: they guarantee your goods are in capable hands.",
    "cert.1":"DIAN authorization","cert.2":"Authorized Economic Operator","cert.3":"Quality management","cert.4":"Secure trade",
    "about.team.t":"People who answer, not forms","about.team.d":"A team of customs agents, classifiers and logistics coordinators who know your operation by name.",
    "team.1.n":"Ricardo Bedoya","team.1.r":"Operations Director","team.2.n":"Ana Lucía Gómez","team.2.r":"Head of Classification","team.3.n":"Julián Ortiz","team.3.r":"Logistics Coordinator","team.4.n":"Carolina Díaz","team.4.r":"Exchange Compliance",
    "about.why.t":"Technical rigor, human service","about.why.1":"We are technically liable for every declaration we sign.","about.why.2":"Online visibility: you always know which channel and stage your cargo is in.","about.why.3":"Transparent quotes, no surprise charges at the end.","about.why.4":"Coverage across the country's main ports and airports.",
    "about.goals.t":"Where we're headed","goals.1.t":"Zero avoidable delays","goals.1.d":"Minimize release times through filings well prepared from the start.","goals.2.t":"Full digitalization","goals.2.d":"Every client follows their operation online, with documents and statuses in one place.","goals.3.t":"Sustainable trade","goals.3.d":"Supporting our clients in secure and responsible trade practices.",
    "svc.hero.t":"All of foreign trade, under one roof","svc.hero.d":"From classifying your product to final delivery, we cover every link in the customs and logistics chain.",
    "svc.import.t":"Imports","svc.import.d":"Customs clearance of goods, import declaration filing, duty assessment and support during DIAN inspection.",
    "svc.export.t":"Exports","svc.export.d":"Export declaration (DEX) filing, document management and coordination so your goods leave the country without setbacks.",
    "svc.clas.t":"Tariff classification","svc.clas.d":"Identifying the correct tariff subheading, classification studies and advance-ruling requests to give your operation certainty.",
    "svc.advice.t":"Foreign trade consulting","svc.advice.d":"Guidance on free trade agreements, special regimes, free-trade zones and tariff-preference opportunities.",
    "svc.fx.t":"Exchange compliance","svc.fx.d":"Preparing exchange declarations and reports to the Banco de la República for import, export and financing operations.",
    "svc.log.t":"Logistics & transport","svc.log.d":"Coordinating international and domestic transport, warehousing, cargo insurance and delivery to your warehouse door.",
    "svc.cta.t":"Not sure where to start?","svc.cta.d":"Describe your operation and we'll tell you exactly what you need and what it costs.",
    "news.hero.t":"Regulation and trade, up to date","news.hero.d":"Changes in customs regulation, calendars and practical tips so your operation is never caught off guard.","news.img":"Article image",
    "news.1.tag":"Regulation","news.1.t":"New controls for textile imports in 2026","news.1.d":"What's changing in labeling requirements and how to prepare to avoid port holds.","news.1.m":"Jan 12, 2026 · 4 min read",
    "news.2.tag":"How-to","news.2.t":"How to read the DIAN traffic light without panicking","news.2.d":"Red, yellow or green: what each channel means and what to have ready in each case.","news.2.m":"Dec 3, 2025 · 6 min read",
    "news.3.tag":"Trade","news.3.t":"Making the most of FTAs: preferences many leave on the table","news.3.d":"A well-managed certificate of origin can save you thousands in duties. Here's how.","news.3.m":"Nov 18, 2025 · 5 min read",
    "contact.hero.t":"Let's talk about your operation","contact.hero.d":"Leave us your cargo details and an advisor will reply with a clear quote in under 24 hours.",
    "form.name":"Full name","form.company":"Company","form.email":"Email","form.type":"Operation type","form.type.import":"Import","form.type.export":"Export","form.type.advice":"Consulting","form.type.other":"Other","form.msg":"Tell us about your cargo",
    "form.terms.1":"I accept the","form.terms.2":"I have read and accept the","form.terms.3":"and the processing of my data","form.submit":"Send request","form.note":"We record the date, time and version of the policies you accept.",
    "contact.addr.t":"Main office","contact.addr.d":"Cargo terminal · Bogotá, Colombia","contact.phone.t":"Phone","contact.mail.t":"Email","contact.hours.t":"Hours","contact.hours.d":"Mon–Fri · 8:00–18:00",
    "legal.eyebrow":"Legal","legal.terms":"terms and conditions","legal.privacy":"privacy policy","legal.cookies":"cookie policy","legal.refund":"refund policy","legal.updated":"Last updated: January 2026",
    "legal.terms.t":"Terms and conditions","legal.terms.box":"This is a template document. It must be reviewed and adapted by a lawyer before publication, in line with Colombian law and Rubicam's actual services.",
    "legal.terms.h1":"1. Purpose","legal.terms.p1":"These terms govern the use of Rubicam's website and the relationship between the agency and users who request information or customs brokerage services.",
    "legal.terms.h2":"2. Services","legal.terms.p2":"Rubicam provides customs brokerage, foreign trade consulting and logistics. The scope of each operation is defined in the corresponding quote and contract.",
    "legal.terms.h3":"3. User responsibility","legal.terms.p3":"The user undertakes to provide truthful and complete information about their goods. The accuracy of the documents provided is essential to correct customs processing.",
    "legal.terms.h4":"4. Intellectual property","legal.terms.p4":"The contents, trademarks and designs of this site belong to Rubicam or its licensors and may not be reproduced without authorization.",
    "legal.terms.h5":"5. Governing law","legal.terms.p5":"These terms are governed by the law of the Republic of Colombia. Any dispute will be submitted to the competent courts of Rubicam's domicile.",
    "legal.privacy.t":"Privacy and data processing policy","legal.privacy.box":"Template document under Colombia's Data Protection Law 1581 of 2012. It must be validated with the data controller and a legal advisor.",
    "legal.privacy.h1":"1. Data controller","legal.privacy.p1":"Rubicam is responsible for processing the personal data collected through this site, in order to handle requests and provide its services.",
    "legal.privacy.h2":"2. Data we collect","legal.privacy.p2":"We collect only the necessary data: name, company, email and the operation details you choose to share. We don't ask for information we don't need.",
    "legal.privacy.h3":"3. Purpose","legal.privacy.p3":"We use your data to answer your request, send quotes and, if you authorize it, share news with you. We do not sell or transfer it to third parties without your consent.",
    "legal.privacy.h4":"4. Your rights","legal.privacy.p4":"You may access, update, rectify and delete your data, and withdraw your authorization, by writing to our contact email.",
    "legal.privacy.h5":"5. Consent record","legal.privacy.p5":"Each time you accept this policy in a form, we store the date, time and version of the document accepted, as proof of the consent given.",
    "legal.cookies.t":"Cookie policy",
    "legal.cookies.h1":"1. What cookies are","legal.cookies.p1":"Cookies are small files stored on your device to remember your preferences, such as the chosen language, and to understand how the site is used.",
    "legal.cookies.h2":"2. Cookies we use","legal.cookies.l1":"<b>Necessary:</b> enable the basic functioning of the site (for example, remembering the language). They require no consent.","legal.cookies.l2":"<b>Analytics:</b> help us measure site usage. They only activate if you accept them.",
    "legal.cookies.h3":"3. Consent","legal.cookies.p3":"When you enter the site we show a notice so you can accept or reject non-essential cookies. You can change your decision at any time by clearing your browser cookies.","legal.cookies.box":"Yes, your site needs consent for analytics or marketing cookies. Purely technical ones (like remembering the language) don't. This site's banner already implements that distinction.",
    "legal.refund.t":"Refund policy","legal.refund.box":"Template document. Adjust the terms and timelines to your actual services before publishing.",
    "legal.refund.h1":"1. Scope","legal.refund.p1":"This policy applies to consulting and management services contracted directly with Rubicam. Customs duties and third-party payments (DIAN, carriers, ports) are governed by their own rules.",
    "legal.refund.h2":"2. Refund requests","legal.refund.p2":"If a service was not provided or was charged in error, you may request a refund within the terms agreed in the contract, by writing to our contact email.",
    "legal.refund.h3":"3. Timelines","legal.refund.p3":"Requests are reviewed and answered within a reasonable period. Where applicable, the refund is made through the same payment method used.",
    "footer.about":"Authorized customs agency. We move your foreign trade with technical rigor and full traceability.","footer.nav":"Navigation","footer.services":"Services","footer.legal":"Legal","footer.rights":"All rights reserved.","footer.cookieSettings":"Cookie settings",
    "cookie.title":"We use cookies","cookie.text":"We use necessary cookies to make the site work and, with your permission, analytics cookies to improve it. See our","cookie.accept":"Accept all","cookie.reject":"Necessary only",
    "_toast.sent":"✓ Request sent. We'll reply within 24 hours.","_toast.consent":"Please accept both checkboxes to continue.","_toast.news":"Sample article — coming soon.","_toast.cookieOk":"Cookie preferences saved."
  },
  de:{
    "nav.home":"Start","nav.about":"Über uns","nav.services":"Leistungen","nav.news":"Aktuelles","nav.contact":"Kontakt","nav.cta":"Anmelden",
    "about.origin":"Unser Ursprung","about.level":"Niveau & Zertifizierungen","about.team":"Unser Team","about.why":"Warum wir?","about.goals":"Unternehmensziele",
    "home.eyebrow":"Zollagentur · DIAN Stufe 1","home.title":"Ihre Fracht, reibungslos abgefertigt.","home.lede":"Wir wickeln Import und Export von A bis Z ab: Zolltarifierung, DIAN-Abfertigung, Devisen-Compliance und Logistik – mit lückenloser Nachverfolgung.","home.cta1":"Angebot anfordern","home.cta2":"Leistungen ansehen",
    "tile.op":"Vorgang · IMPO-2024-0192","tile.port":"Hafen","tile.channel":"Kanal","tile.green":"Grün","tile.status":"Status","tile.cleared":"Freigabe erteilt",
    "trust.title":"Vertrauen in unsere Abwicklung",
    "feat.eyebrow":"Was wir tun","feat.title":"Eine Agentur, die ganze Kette","feat.lede":"Wir decken jede Phase des Außenhandels ab, damit Sie sich auf Ihr Geschäft konzentrieren können, nicht auf Formalitäten.",
    "feat.1.t":"Import & Export","feat.1.d":"Vollständige DIAN-Abfertigung, Erstellung der Anmeldungen und Begleitung bei physischer oder dokumentarischer Prüfung.",
    "feat.2.t":"Zolltarifierung","feat.2.d":"Wir bestimmen die richtige Unterposition, um Mehrkosten, Strafen und Verzögerungen bei der Freigabe Ihrer Ware zu vermeiden.",
    "feat.3.t":"Devisen-Compliance","feat.3.d":"Devisenanmeldungen und Meldungen an die Banco de la República, mit der regulatorischen Absicherung, die Ihr Vorgang verlangt.",
    "more":"Mehr erfahren →",
    "stat.1":"Jahre Erfahrung","stat.2":"Freigaben ohne Beanstandung","stat.3":"Herkunfts- & Zielländer","stat.4":"Durchschnittliche Reaktionszeit",
    "why.eyebrow":"Warum Rubicam","why.title":"Verantwortung hinter jeder Anmeldung","why.lede":"Als autorisierte Zollagentur haften wir fachlich für jeden Vorgang. Diese Verantwortung bedeutet Sorgfalt, nicht Bürokratie.",
    "why.1":"Gültige DIAN-Zulassung und vertrauenswürdiger Betreiber","why.2":"Ein fester Koordinator pro Kunde, kein Callcenter","why.3":"Online-Nachverfolgung des Status jedes Vorgangs","why.4":"Erfüllungs- und Datenschutzversicherungen",
    "why.panel.eyebrow":"Betriebsübersicht","why.panel.1":"Anmeldungen diesen Monat","why.panel.2":"In Prüfung","why.panel.3":"Freigabe erteilt","why.panel.4":"SLA erfüllt",
    "quote.text":"Wir wussten vorher nie, wo unsere Fracht war – jetzt sehen wir den Status jedes Containers in Echtzeit. Rubicam hat uns die Unsicherheit abgenommen.","quote.name":"María Camila Restrepo","quote.role":"Importleiterin · Textiles del Norte",
    "cta.title":"Fracht abzufertigen?","cta.lede":"Erzählen Sie uns von Ihrem Vorgang und wir senden Ihnen in unter 24 Stunden ein klares Angebot, ohne Kleingedrucktes.","cta.btn":"Mit einem Berater sprechen",
    "about.hero.t":"Seit über zwei Jahrzehnten im Außenhandel des Landes","about.hero.d":"Wir sind eine kolumbianische Zollagentur, die Komplexes einfach macht: Zollvorschriften in reibungslose Abläufe verwandeln.",
    "about.origin.t":"Vom Hafenbüro zum strategischen Partner","about.origin.d1":"Rubicam entstand am Kai, beim Lösen von Vorgängen, die andere für unmöglich hielten. Über die Jahre haben wir dieses Praxiswissen in Prozesse, Technologie und ein Team verwandelt, das weiß: Hinter jeder Anmeldung wartet ein Geschäft.","about.origin.d2":"Heute begleiten wir Importeure und Exporteure jeder Größe – vom KMU beim ersten Auslandseinkauf bis zu großvolumigen Vorgängen unter Sonderregelungen.",
    "about.level.t":"Zugelassen, geprüft und abgesichert","about.level.d":"Wir arbeiten nach den Zulassungen und Standards, die der kolumbianische Außenhandel verlangt. Unsere Zertifizierungen sind keine Zierde, sondern die Garantie, dass Ihre Ware in fähigen Händen ist.",
    "cert.1":"DIAN-Zulassung","cert.2":"Zugelassener Wirtschaftsbeteiligter","cert.3":"Qualitätsmanagement","cert.4":"Sicherer Handel",
    "about.team.t":"Menschen, die antworten, keine Formulare","about.team.d":"Ein Team aus Zollagenten, Tarifierern und Logistikkoordinatoren, das Ihren Vorgang beim Namen kennt.",
    "team.1.n":"Ricardo Bedoya","team.1.r":"Betriebsleiter","team.2.n":"Ana Lucía Gómez","team.2.r":"Leiterin Tarifierung","team.3.n":"Julián Ortiz","team.3.r":"Logistikkoordinator","team.4.n":"Carolina Díaz","team.4.r":"Devisen-Compliance",
    "about.why.t":"Fachliche Sorgfalt mit menschlichem Service","about.why.1":"Wir haften fachlich für jede Anmeldung, die wir unterschreiben.","about.why.2":"Online-Transparenz: Sie wissen immer, in welchem Kanal und welcher Phase Ihre Fracht ist.","about.why.3":"Transparente Angebote, keine Überraschungskosten am Ende.","about.why.4":"Abdeckung der wichtigsten Häfen und Flughäfen des Landes.",
    "about.goals.t":"Wohin wir gehen","goals.1.t":"Null vermeidbare Verzögerungen","goals.1.d":"Freigabezeiten minimieren durch von Anfang an gut vorbereitete Vorgänge.","goals.2.t":"Vollständige Digitalisierung","goals.2.d":"Jeder Kunde verfolgt seinen Vorgang online, mit Dokumenten und Status an einem Ort.","goals.3.t":"Nachhaltiger Handel","goals.3.d":"Unsere Kunden bei sicheren und verantwortungsvollen Handelspraktiken begleiten.",
    "svc.hero.t":"Der gesamte Außenhandel unter einem Dach","svc.hero.d":"Von der Tarifierung Ihres Produkts bis zur Endauslieferung decken wir jedes Glied der Zoll- und Logistikkette ab.",
    "svc.import.t":"Importe","svc.import.d":"Verzollung der Ware, Erstellung der Einfuhranmeldung, Abgabenberechnung und Begleitung bei der DIAN-Prüfung.",
    "svc.export.t":"Exporte","svc.export.d":"Erstellung der Ausfuhranmeldung (DEX), Dokumentenmanagement und Koordination, damit Ihre Ware reibungslos das Land verlässt.",
    "svc.clas.t":"Zolltarifierung","svc.clas.d":"Bestimmung der richtigen Zoll-Unterposition, Tarifierungsgutachten und verbindliche Zolltarifauskünfte für Rechtssicherheit.",
    "svc.advice.t":"Außenhandelsberatung","svc.advice.d":"Begleitung bei Freihandelsabkommen, Sonderregelungen, Freizonen und der Nutzung von Zollpräferenzen.",
    "svc.fx.t":"Devisen-Compliance","svc.fx.d":"Erstellung von Devisenanmeldungen und Meldungen an die Banco de la República für Import-, Export- und Finanzierungsvorgänge.",
    "svc.log.t":"Logistik & Transport","svc.log.d":"Koordination von internationalem und nationalem Transport, Lagerung, Frachtversicherung und Zustellung bis an Ihr Lagertor.",
    "svc.cta.t":"Unsicher, wo Sie anfangen sollen?","svc.cta.d":"Beschreiben Sie Ihren Vorgang und wir sagen Ihnen genau, was Sie brauchen und was es kostet.",
    "news.hero.t":"Vorschriften und Außenhandel, aktuell","news.hero.d":"Änderungen in den Zollvorschriften, Kalender und praktische Tipps, damit Ihr Vorgang nie überrascht wird.","news.img":"Artikelbild",
    "news.1.tag":"Vorschriften","news.1.t":"Neue Kontrollen für Textilimporte 2026","news.1.d":"Was sich bei den Kennzeichnungspflichten ändert und wie Sie sich vorbereiten, um Hafensperren zu vermeiden.","news.1.m":"12. Jan. 2026 · 4 Min. Lesezeit",
    "news.2.tag":"Ratgeber","news.2.t":"Die DIAN-Ampel lesen, ohne in Panik zu geraten","news.2.d":"Rot, Gelb oder Grün: Was jeder Kanal bedeutet und was Sie jeweils bereithalten sollten.","news.2.m":"3. Dez. 2025 · 6 Min. Lesezeit",
    "news.3.tag":"Handel","news.3.t":"Freihandelsabkommen nutzen: Präferenzen, die viele liegen lassen","news.3.d":"Ein gut verwaltetes Ursprungszeugnis kann Ihnen Tausende an Zöllen sparen. So geht's.","news.3.m":"18. Nov. 2025 · 5 Min. Lesezeit",
    "contact.hero.t":"Sprechen wir über Ihren Vorgang","contact.hero.d":"Hinterlassen Sie uns Ihre Frachtdaten und ein Berater antwortet mit einem klaren Angebot in unter 24 Stunden.",
    "form.name":"Vollständiger Name","form.company":"Unternehmen","form.email":"E-Mail","form.type":"Art des Vorgangs","form.type.import":"Import","form.type.export":"Export","form.type.advice":"Beratung","form.type.other":"Sonstiges","form.msg":"Erzählen Sie uns von Ihrer Fracht",
    "form.terms.1":"Ich akzeptiere die","form.terms.2":"Ich habe die","form.terms.3":"und die Verarbeitung meiner Daten gelesen und akzeptiere sie","form.submit":"Anfrage senden","form.note":"Wir erfassen Datum, Uhrzeit und Version der von Ihnen akzeptierten Richtlinien.",
    "contact.addr.t":"Hauptbüro","contact.addr.d":"Frachtterminal · Bogotá, Kolumbien","contact.phone.t":"Telefon","contact.mail.t":"E-Mail","contact.hours.t":"Öffnungszeiten","contact.hours.d":"Mo–Fr · 8:00–18:00",
    "legal.eyebrow":"Rechtliches","legal.terms":"Allgemeine Geschäftsbedingungen","legal.privacy":"Datenschutzerklärung","legal.cookies":"Cookie-Richtlinie","legal.refund":"Erstattungsrichtlinie","legal.updated":"Zuletzt aktualisiert: Januar 2026",
    "legal.terms.t":"Allgemeine Geschäftsbedingungen","legal.terms.box":"Dies ist ein Musterdokument. Es muss vor der Veröffentlichung von einem Anwalt geprüft und an das kolumbianische Recht sowie die tatsächlichen Leistungen von Rubicam angepasst werden.",
    "legal.terms.h1":"1. Gegenstand","legal.terms.p1":"Diese Bedingungen regeln die Nutzung der Website von Rubicam und das Verhältnis zwischen der Agentur und den Nutzern, die Informationen oder Zolldienstleistungen anfragen.",
    "legal.terms.h2":"2. Leistungen","legal.terms.p2":"Rubicam erbringt Zollabfertigung, Außenhandelsberatung und Logistik. Der Umfang jedes Vorgangs wird im jeweiligen Angebot und Vertrag festgelegt.",
    "legal.terms.h3":"3. Verantwortung des Nutzers","legal.terms.p3":"Der Nutzer verpflichtet sich, wahrheitsgemäße und vollständige Angaben zu seiner Ware zu machen. Die Richtigkeit der bereitgestellten Dokumente ist für die korrekte Zollabwicklung entscheidend.",
    "legal.terms.h4":"4. Geistiges Eigentum","legal.terms.p4":"Die Inhalte, Marken und Designs dieser Website gehören Rubicam oder seinen Lizenzgebern und dürfen ohne Genehmigung nicht reproduziert werden.",
    "legal.terms.h5":"5. Anwendbares Recht","legal.terms.p5":"Diese Bedingungen unterliegen dem Recht der Republik Kolumbien. Streitigkeiten werden den zuständigen Gerichten am Sitz von Rubicam vorgelegt.",
    "legal.privacy.t":"Datenschutz- und Datenverarbeitungsrichtlinie","legal.privacy.box":"Musterdokument gemäß dem kolumbianischen Datenschutzgesetz 1581 von 2012. Es muss mit dem Verantwortlichen und einem Rechtsberater abgestimmt werden.",
    "legal.privacy.h1":"1. Verantwortlicher","legal.privacy.p1":"Rubicam ist für die Verarbeitung der über diese Website erhobenen personenbezogenen Daten verantwortlich, um Anfragen zu bearbeiten und seine Leistungen zu erbringen.",
    "legal.privacy.h2":"2. Welche Daten wir erheben","legal.privacy.p2":"Wir erheben nur die notwendigen Daten: Name, Unternehmen, E-Mail und die Vorgangsdetails, die Sie teilen möchten. Wir fragen keine Informationen ab, die wir nicht benötigen.",
    "legal.privacy.h3":"3. Zweck","legal.privacy.p3":"Wir verwenden Ihre Daten, um Ihre Anfrage zu beantworten, Angebote zu senden und, falls Sie es erlauben, Ihnen Neuigkeiten mitzuteilen. Wir verkaufen oder übertragen sie nicht ohne Ihre Einwilligung an Dritte.",
    "legal.privacy.h4":"4. Ihre Rechte","legal.privacy.p4":"Sie können Ihre Daten einsehen, aktualisieren, berichtigen und löschen sowie die Einwilligung widerrufen, indem Sie an unsere Kontakt-E-Mail schreiben.",
    "legal.privacy.h5":"5. Einwilligungsnachweis","legal.privacy.p5":"Jedes Mal, wenn Sie diese Richtlinie in einem Formular akzeptieren, speichern wir Datum, Uhrzeit und Version des akzeptierten Dokuments als Nachweis der erteilten Einwilligung.",
    "legal.cookies.t":"Cookie-Richtlinie",
    "legal.cookies.h1":"1. Was Cookies sind","legal.cookies.p1":"Cookies sind kleine Dateien, die auf Ihrem Gerät gespeichert werden, um Ihre Präferenzen – etwa die gewählte Sprache – zu merken und zu verstehen, wie die Website genutzt wird.",
    "legal.cookies.h2":"2. Verwendete Cookies","legal.cookies.l1":"<b>Notwendige:</b> ermöglichen die Grundfunktion der Website (z. B. das Merken der Sprache). Sie erfordern keine Einwilligung.","legal.cookies.l2":"<b>Analyse:</b> helfen uns, die Nutzung zu messen. Sie werden nur aktiviert, wenn Sie zustimmen.",
    "legal.cookies.h3":"3. Einwilligung","legal.cookies.p3":"Beim Betreten der Website zeigen wir einen Hinweis, damit Sie nicht notwendige Cookies annehmen oder ablehnen können. Sie können Ihre Entscheidung jederzeit ändern, indem Sie die Cookies in Ihrem Browser löschen.","legal.cookies.box":"Ja, Ihre Website benötigt eine Einwilligung für Analyse- oder Marketing-Cookies. Rein technische (wie das Merken der Sprache) nicht. Das Banner dieser Website setzt diese Unterscheidung bereits um.",
    "legal.refund.t":"Erstattungsrichtlinie","legal.refund.box":"Musterdokument. Passen Sie die Fristen und Bedingungen vor der Veröffentlichung an Ihre tatsächlichen Leistungen an.",
    "legal.refund.h1":"1. Geltungsbereich","legal.refund.p1":"Diese Richtlinie gilt für Beratungs- und Managementleistungen, die direkt mit Rubicam vereinbart wurden. Zollabgaben und Zahlungen an Dritte (DIAN, Frachtführer, Häfen) unterliegen ihren eigenen Regeln.",
    "legal.refund.h2":"2. Erstattungsanträge","legal.refund.p2":"Wurde eine Leistung nicht erbracht oder irrtümlich berechnet, können Sie innerhalb der im Vertrag vereinbarten Fristen eine Erstattung beantragen, indem Sie an unsere Kontakt-E-Mail schreiben.",
    "legal.refund.h3":"3. Fristen","legal.refund.p3":"Anträge werden innerhalb einer angemessenen Frist geprüft und beantwortet. Sofern berechtigt, erfolgt die Erstattung über dasselbe verwendete Zahlungsmittel.",
    "footer.about":"Autorisierte Zollagentur. Wir bewegen Ihren Außenhandel mit fachlicher Sorgfalt und lückenloser Nachverfolgung.","footer.nav":"Navigation","footer.services":"Leistungen","footer.legal":"Rechtliches","footer.rights":"Alle Rechte vorbehalten.","footer.cookieSettings":"Cookie-Einstellungen",
    "cookie.title":"Wir verwenden Cookies","cookie.text":"Wir verwenden notwendige Cookies, damit die Website funktioniert, und mit Ihrer Erlaubnis Analyse-Cookies zur Verbesserung. Siehe unsere","cookie.accept":"Alle akzeptieren","cookie.reject":"Nur notwendige",
    "_toast.sent":"✓ Anfrage gesendet. Wir antworten innerhalb von 24 Stunden.","_toast.consent":"Bitte akzeptieren Sie beide Kontrollkästchen, um fortzufahren.","_toast.news":"Beispielartikel — in Kürze verfügbar.","_toast.cookieOk":"Cookie-Einstellungen gespeichert."
  }
};

/* ---- Motor de idioma ---- */
function lsGet(k){try{return localStorage.getItem(k)}catch(e){return null}}
function lsSet(k,v){try{localStorage.setItem(k,v)}catch(e){}}
function getLang(){return lsGet('rubicam_lang')||'es'}

/* Objeto global con los textos de aviso del idioma activo */
window.__T = {};

function applyLang(lang){
  const dict = I18N[lang] || I18N.es;
  document.querySelectorAll('[data-i18n]').forEach(el=>{
    const key = el.getAttribute('data-i18n');
    if(dict[key]!==undefined){
      if(dict[key].indexOf('<b>')!==-1) el.innerHTML = dict[key];
      else el.textContent = dict[key];
    }
  });
  document.documentElement.lang = lang;
  const label = document.getElementById('langLabel');
  if(label) label.textContent = lang.toUpperCase();
  document.querySelectorAll('.lang-menu button').forEach(b=>{
    b.classList.toggle('sel', b.getAttribute('data-lang')===lang);
  });
  window.__T = {
    sent:dict['_toast.sent'], consent:dict['_toast.consent'],
    news:dict['_toast.news'], cookieOk:dict['_toast.cookieOk']
  };
  lsSet('rubicam_lang', lang);
}
/* Exportación solo para las pruebas automáticas (Node/Jest). En el navegador no hace nada. */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { I18N, applyLang, getLang, lsGet, lsSet };
}

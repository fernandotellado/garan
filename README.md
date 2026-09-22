# EU legal guarantee notice for WooCommerce

**[English](#english) · [Español](#español)**

Snippets to display in WooCommerce the EU harmonised notice on the legal guarantee of conformity required by Commission Implementing Regulation (EU) 2025/1960 from 27 September 2026, plus the Spanish three-year note.

**Full explanation (in Spanish) · Explicación completa:** [ayudawp.com/garan](https://ayudawp.com/garan/)

---

## English

> This is not legal advice. Check with your legal advisor how it applies to your store.

**Full explanation** (in Spanish), with a review of the plugins available: [ayudawp.com/garan](https://ayudawp.com/garan/)

### Prefer a plugin? EU Withdrawal Compliance

If you would rather not deal with code, [EU Withdrawal Compliance](https://wordpress.org/plugins/eu-withdrawal-compliance/), my plugin for the mandatory EU withdrawal function, includes the legal guarantee notice since version 2.3.0:

- Bundles the official PNG and PDF files in the 24 EU languages, so there is nothing to download or upload. The English PNG is rendered from the official English PDF, because the Commission's PNG package has no English version.
- Shows the notice right above the place order button, in both the classic and the block checkout, with four display modes (full by default, expandable, popover or nothing in the checkout).
- Adds it to the customer confirmation emails with the official PDF attached, in the order language if you use WPML or Polylang with WooCommerce.
- Uses the Spanish notice on Catalan, Basque and Galician sites, and the official language of their country for other languages without an official notice.
- Adds the Spanish three-year note when the store is based in Spain.
- Includes the `[ayudawp_guarantee_link]` shortcode to link your legal guarantee page from the footer or anywhere else.
- Only shows up when the cart contains physical products, and adds an order note with the notice shown.

On new installs the module comes enabled. If you update from an earlier version it arrives disabled, with a dashboard notice to enable it in one click. It does not handle the GARAN label yet.

If you already use it for the withdrawal function, just update it and you won't need any of the snippets in this repository.

### What's inside

| File | What it does |
|---|---|
| `ayudawp-garantia-legal.php` | Standalone snippet. No plugin needed. |
| `addons/ayudawp-garan-addon-arthur-smith.php` | Add-on for the *Arthur Smith's EU Guarantee Notice and GARAN Label* plugin. |
| `addons/ayudawp-garan-addon-garanlabs.php` | Add-on for the *EU Legal-Guarantee Notice & GARAN Durability Label for WooCommerce* plugin (GaranLabs). |

Use only one of the three.

### Standalone snippet

**What it does**

- Shows the official notice right above the place order button, in both the classic and the block checkout.
- Adds it to the customer confirmation emails (on-hold and processing) and attaches the official PDF, because many email clients block images.
- Adds a note below it with the three-year legal guarantee for new goods in Spain and a link to your terms. Only when the store's base country is Spain.
- Picks the notice language from the site language. Languages without an official notice use the official language of their country (Catalan, Basque and Galician use Spanish; Luxembourgish, French; Welsh and Scottish Gaelic, English), and any language without a file uses the fallback, Spanish unless you change it.
- Only shows up when the cart or the order contains at least one physical product.
- Adds an order note recording which notice was shown at checkout, only once per order even if the customer retries a failed payment.

It does not handle the GARAN label, which is only needed when the producer offers a commercial guarantee of durability longer than two years. Use one of the plugins for that.

**Installation**

1. Download the official files from the [European Commission page](https://commission.europa.eu/publications/practical-guidelines-and-high-resolution-vector-files-eu-notice-and-label-product-guarantees_en). You need the colour PNG in your language (“PNG and JPG” package, e.g. `Legal guarantee_notice_ES.png`) and the PDF (PDF package, e.g. `Legal guarantee_notice ESN.pdf`). The PNG package has no English version. For English, export the first page of the official English PDF to PNG at the same size as the others (1654 × 2339 pixels) without changing anything, and use the PDF for the emails.
2. Upload them to the Media Library as they are, without editing.
3. Write down the ID of each file. It appears in the browser address bar when you open the file in **Media > Library** (`post.php?post=123` or `?item=123`).
4. Put those IDs in the `ayudawp_gl_settings()` function.
5. Add the snippet with a snippets plugin or to your child theme's `functions.php` (without the opening `<?php` line). It also works as a must-use plugin in `wp-content/mu-plugins/`.
6. Place a test order and check the checkout and the email you receive.

**Settings** (all in `ayudawp_gl_settings()`)

| Setting | What it does | Default |
|---|---|---|
| `images` | Attachment ID of the colour PNG per language (two-letter code). | `'es' => 0` |
| `pdfs` | Attachment ID of the PDF per language, attached to the emails. Only real PDF files inside the uploads folder are attached. | `'es' => 0` |
| `languages` | Languages without an official notice and the official language they use instead. | `ca`, `eu`, `gl` → `es`; `lb` → `fr`; `cy`, `gd` → `en` |
| `fallback` | Language used when there is no file for the site language. | `'es'` |
| `checkout` | Notice above the place order button. | `true` |
| `email` | Notice inside the customer emails. | `true` |
| `pdf` | Official PDF attached to those emails. | `true` |
| `note` | Spanish three-year note (only for stores based in Spain). | `true` |
| `record` | Order note with the notice shown. | `true` |
| `display` | Display mode: `full`, `details` or `popover`. | `'full'` |
| `email_ids` | Emails that get the notice and the PDF. | On-hold and processing |
| `terms_anchor` | Anchor of the guarantee section in your terms. | `'garantia-legal'` |

**Which display mode**

- **`full`:** the whole notice, always visible. The safest option, but in themes with a narrow order column (such as Storefront in the classic checkout) it drops below 300 pixels wide and becomes unreadable.
- **`details`:** a “Your legal guarantee rights” line (in Spanish in the snippet) that expands the notice below. It is the example given in the Commission guidelines, although some read the Regulation as allowing that format only for the GARAN label.
- **`popover`:** a button that opens the notice large over the page, up to 820 pixels wide, using the browser's native popover, with no JavaScript. The best choice for narrow columns.

On mobile, all three modes take the screen width, so the image links to the full-size file for zooming.

**Tested with** WordPress 7.1.1, WooCommerce 11.1.1 and PHP 8.4 (syntax compatible with PHP 7.4), classic and block checkout, Twenty Twenty-Five and Storefront themes, HPOS enabled and disabled.

### Add-on for Arthur Smith's EU Guarantee Notice and GARAN Label

The plugin places the notice at the top of the checkout page. This add-on moves it right above the place order button in both checkouts, uses the Spanish notice on Catalan, Basque and Galician sites and adds the three-year note.

In the plugin settings:

- In **Placement**, untick “Checkout page” so the notice is not shown twice.
- In **Reveal trigger text**, write the trigger text in your language. Otherwise it shows “Your legal guarantee rights”.
- If your site is in Catalan, Basque or Galician, choose Spanish in **Language**, so the product page and the emails also use the Spanish notice.

### Add-on for GaranLabs

Same idea: Spanish notice on Catalan, Basque and Galician sites (with **Display language** on automatic), the notice right above the place order button in both checkouts using your **Display mode**, and the three-year note. In the plugin settings, keep **Checkout page** off.

What the add-on does not change (plugin version 1.9.2):

- If you fill in the feedback form when deactivating the plugin, it sends the text, your site address and the administrator email to an external server, although its listing says it sends no data anywhere.
- Its notice images are not the official files: they are cropped and use a different blue.
- The GARAN label does not show for variable products in the block checkout or in the emails.
- The GARAN attachment format in emails can cause a fatal error.
- In popup mode the notice opens about 420 pixels wide.

### License

GPL-2.0-or-later. The official notice files belong to the European Commission and are not included in this repository.

---

## Español

> Esto no es asesoramiento legal. Consulta con tu asesoría cómo te afecta.

**Explicación completa**, con el repaso de los plugins que hay: [ayudawp.com/garan](https://ayudawp.com/garan/)

### ¿Prefieres un plugin? EU Withdrawal Compliance

Si prefieres no tocar código, [EU Withdrawal Compliance](https://wordpress.org/plugins/eu-withdrawal-compliance/), mi plugin para la función de desistimiento obligatoria en la UE, incluye el aviso de garantía legal desde la versión 2.3.0:

- Lleva dentro el PNG y el PDF oficiales en los 24 idiomas de la UE, así que no tienes que descargar ni subir nada. El PNG en inglés está convertido desde el PDF oficial en inglés, porque el paquete de PNG de la Comisión no lo trae.
- Muestra el aviso justo encima del botón de realizar el pedido, en el checkout clásico y en el de bloques, y puedes elegir entre verlo completo (el modo por defecto), desplegable, en una ventana emergente o no mostrarlo en el checkout.
- Lo incluye en los correos de confirmación al cliente con el PDF oficial adjunto, en el idioma del pedido si usas WPML o Polylang con WooCommerce.
- Usa el aviso en castellano en webs en catalán, euskera o gallego, y en otras lenguas sin aviso oficial, el del idioma oficial de su país.
- Añade la nota de los tres años cuando la tienda está en España.
- Trae el shortcode `[ayudawp_guarantee_link]` para enlazar tu página de garantía legal desde el pie o desde donde quieras.
- Solo aparece si el carrito lleva productos físicos y deja una nota en cada pedido con el aviso que se mostró.

En instalaciones nuevas el módulo viene activado. Si actualizas desde una versión anterior llega desactivado, con un aviso en el escritorio para activarlo en un clic. Todavía no incluye la etiqueta GARAN.

Si ya lo usas para el desistimiento, te basta con actualizarlo y no necesitas ninguno de los snippets de este repositorio.

### Qué hay aquí

| Archivo | Para qué sirve |
|---|---|
| `ayudawp-garantia-legal.php` | Snippet independiente. No necesita ningún plugin. |
| `addons/ayudawp-garan-addon-arthur-smith.php` | Complemento para el plugin *Arthur Smith's EU Guarantee Notice and GARAN Label*. |
| `addons/ayudawp-garan-addon-garanlabs.php` | Complemento para el plugin *EU Legal-Guarantee Notice & GARAN Durability Label for WooCommerce* (GaranLabs). |

Usa solo uno de los tres.

### Snippet independiente

**Qué hace**

- Muestra el aviso oficial justo encima del botón de realizar el pedido, tanto en el checkout clásico como en el de bloques.
- Lo incluye en los correos de confirmación al cliente (pedido en espera y en proceso) y adjunta el PDF oficial, porque muchos programas de correo bloquean las imágenes.
- Pone debajo una nota con los tres años de garantía de los bienes nuevos en España y un enlace a tus términos. Solo aparece si el país base de la tienda es España.
- Elige el idioma del aviso según el de la web. Las lenguas sin aviso oficial usan la oficial de su país (el catalán, el euskera y el gallego, el castellano; el luxemburgués, el francés; el galés y el gaélico escocés, el inglés), y cualquier idioma sin archivo usa el de reserva, que es el castellano si no lo cambias.
- Solo se muestra si el carrito o el pedido lleva algún producto físico.
- Deja una nota en cada pedido con el aviso que se mostró en el checkout, una sola vez aunque el cliente reintente un pago fallido.

No incluye la etiqueta GARAN, que solo hace falta si el fabricante ofrece una garantía comercial de durabilidad de más de dos años. Para eso usa uno de los plugins.

**Instalación**

1. Descarga los archivos oficiales desde la [página de la Comisión Europea](https://commission.europa.eu/publications/practical-guidelines-and-high-resolution-vector-files-eu-notice-and-label-product-guarantees_en). Necesitas el PNG en color de tu idioma (paquete «PNG and JPG», archivo `Legal guarantee_notice_ES.png`) y el PDF (paquete de PDF, archivo `Legal guarantee_notice ESN.pdf`). El paquete de PNG no trae inglés. Para el inglés, exporta a PNG la primera página del PDF oficial en inglés al mismo tamaño que los demás (1654 × 2339 píxeles) sin cambiar nada, y usa el PDF para los correos.
2. Súbelos a la biblioteca de medios tal cual, sin editarlos.
3. Apunta el ID de cada uno. Aparece en la dirección del navegador al abrir el archivo en **Medios > Biblioteca** (`post.php?post=123` o `?item=123`).
4. Escribe esos IDs en la función `ayudawp_gl_settings()`.
5. Añade el snippet con un plugin de snippets o en el `functions.php` de tu tema hijo, en este caso sin la línea `<?php` del principio. También funciona como mu-plugin dentro de `wp-content/mu-plugins/`.
6. Haz un pedido de prueba y revisa el checkout y el correo que te llega.

**Ajustes** (todos en `ayudawp_gl_settings()`)

| Ajuste | Qué hace | Por defecto |
|---|---|---|
| `images` | ID del PNG en color por idioma (código de dos letras). | `'es' => 0` |
| `pdfs` | ID del PDF por idioma, para adjuntarlo a los correos. Solo se adjuntan archivos PDF de verdad que estén dentro de la carpeta de subidas. | `'es' => 0` |
| `languages` | Lenguas sin aviso oficial y la oficial que usan en su lugar. | `ca`, `eu`, `gl` → `es`; `lb` → `fr`; `cy`, `gd` → `en` |
| `fallback` | Idioma que se usa cuando no hay archivo para el de la web. | `'es'` |
| `checkout` | Aviso encima del botón de realizar el pedido. | `true` |
| `email` | Aviso dentro de los correos al cliente. | `true` |
| `pdf` | PDF oficial adjunto a esos correos. | `true` |
| `note` | Nota de los tres años (solo con la tienda en España). | `true` |
| `record` | Nota en el pedido con el aviso mostrado. | `true` |
| `display` | Modo de visualización: `full`, `details` o `popover`. | `'full'` |
| `email_ids` | Correos que llevan el aviso y el PDF. | En espera y en proceso |
| `terms_anchor` | Ancla de la sección de garantía en tus términos. | `'garantia-legal'` |

**Qué modo elegir**

- **`full`:** el aviso completo, a la vista. Es la opción más prudente, pero en temas con la columna del pedido estrecha (como Storefront en el checkout clásico) el aviso se queda por debajo de 300 píxeles de ancho y no se lee.
- **`details`:** una línea «Consulta tus derechos de garantía legal» que despliega el aviso debajo. Es lo que ponen como ejemplo las directrices de la Comisión, aunque hay quien interpreta que el reglamento solo permite ese formato para la etiqueta GARAN.
- **`popover`:** un botón que abre el aviso grande por encima de la página, hasta 820 píxeles de ancho, con el popover nativo del navegador y sin JavaScript. Es la mejor opción si la columna es estrecha.

En el móvil los tres modos quedan al ancho de la pantalla, así que la imagen enlaza al archivo a tamaño completo para que se pueda ampliar.

**Probado con** WordPress 7.1.1, WooCommerce 11.1.1 y PHP 8.4 (la sintaxis es compatible con PHP 7.4), en los checkouts clásico y de bloques, con los temas Twenty Twenty-Five y Storefront y con HPOS activado y desactivado.

### Complemento para Arthur Smith's EU Guarantee Notice and GARAN Label

El plugin pone el aviso arriba del todo en la página de pago. Este complemento lo coloca justo encima del botón en los dos checkouts, usa el aviso en castellano en webs en catalán, euskera o gallego y añade la nota de los tres años.

En los ajustes del plugin:

- En **Placement**, desmarca «Checkout page» para que el aviso no salga dos veces.
- En **Reveal trigger text**, escribe el texto del desplegable en castellano. Si no, aparece «Your legal guarantee rights».
- Si tu web está en catalán, euskera o gallego, elige español en **Language**, para que la ficha de producto y los correos también salgan en castellano.

### Complemento para GaranLabs

Hace lo mismo: el aviso en castellano en webs en catalán, euskera o gallego (con **Display language** en automático), el aviso encima del botón en los dos checkouts con el modo que tengas en **Display mode**, y la nota de los tres años. En los ajustes del plugin, deja desactivado **Checkout page**.

Lo que el complemento no cambia (versión 1.9.2 del plugin):

- Si al desactivar el plugin rellenas el formulario de opinión, envía el texto, la dirección de tu web y el correo del administrador a un servidor externo, aunque su ficha dice que no envía datos a ningún sitio.
- Sus imágenes del aviso no son los archivos oficiales tal cual, están recortadas y tienen otro azul.
- La etiqueta GARAN no aparece con productos variables en el checkout de bloques ni en los correos.
- El formato de adjunto de la etiqueta GARAN en los correos puede provocar un error fatal.
- En el modo de ventana emergente el aviso se abre a unos 420 píxeles de ancho.

### Licencia

GPL-2.0-or-later. Los archivos oficiales del aviso son de la Comisión Europea y no se incluyen en este repositorio.

---

## Support

Need help or have suggestions?

- [Official website](https://servicios.ayudawp.com)
- [Issues on GitHub](../../issues)
- [YouTube channel](https://www.youtube.com/AyudaWordPressES)
- [Documentation and tutorials](https://ayudawp.com)

Found these snippets useful? Star the repository and help spread the word!

## About AyudaWP.com

We are specialists in WordPress security, SEO, AI and performance optimization plugins. We create tools that solve real problems for WordPress site owners while maintaining the highest coding standards and accessibility requirements.
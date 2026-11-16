# cursolandia
# Cursolandia

Cursolandia es una plataforma web donde los usuarios pueden aprender, crear cursos y compartir sus conocimientos con otras personas. La idea es que cualquier persona pueda encontrar cursos sobre temas que le interesen y también tenga la posibilidad de crear sus propias propuestas para enseñar algo que conoce.

**Página web:** https://cursolandia.alwaysdata.net

## Cómo organicé el proyecto

Para desarrollar Cursolandia también me apoyé en el trabajo que realicé en la materia Ingeniería de Software. En esa materia fui trabajando con diagramas, modelos y mapas de navegación que me ayudaron a organizar cómo iba a funcionar la página, qué opciones tendría cada usuario y cómo se conectarían las diferentes pantallas.

El mapa de navegación inicial quedó dividido en 17 pantallas y apartados. Desde la página principal, una persona puede registrarse o iniciar sesión y después acceder al inicio de la plataforma. Desde ahí puede buscar cursos, consultar su perfil, revisar notificaciones, gestionar sus inscripciones o crear un curso.

Cada opción tiene su propio recorrido. Por ejemplo, cuando una persona encuentra un curso, primero puede consultar su información y después solicitar la inscripción. Una vez dentro, puede acceder a los materiales y participar en el espacio de preguntas y respuestas.

Más adelante agregué otras funciones, como la administración de solicitudes del Plan Pro, la consulta de comprobantes y la opción de cerrar sesión.

## Referencias para el diseño

Durante el desarrollo fui tomando algunas ideas de otras plataformas que ya ofrecen servicios parecidos. Hotmart me sirvió como referencia para la presentación de los cursos y algunas partes de la página de inicio. En Skool observé cómo estaban organizadas las comunidades, los cursos y los espacios de participación. También utilicé Google Classroom como referencia para pensar la distribución de los materiales y el contenido de los cursos.

Otra referencia fue YouTube, especialmente por la forma en que presenta las notificaciones y permite identificar nuevas actividades.

A partir de estas páginas fui tomando ideas y adaptándolas a lo que necesitaba Cursolandia, tratando de mantener una organización sencilla para que los usuarios puedan encontrar las opciones sin complicaciones.

## Cómo está organizado el código

Aunque en el mapa de navegación las pantallas están divididas según el recorrido que realiza el usuario, los archivos del proyecto están organizados de otra manera.

En la carpeta principal están los archivos PHP que muestran las distintas páginas y permiten realizar acciones, como iniciar sesión, completar el registro, crear cursos, consultar el perfil, administrar solicitudes o descargar materiales.

Después fui separando el resto del código en carpetas. En `componentes` están las partes que se utilizan en diferentes páginas, como el menú lateral y las notificaciones. En `clases` se encuentra la programación que maneja los usuarios, los cursos, las solicitudes y las notificaciones.

La carpeta `bd` contiene la conexión con MySQL y los archivos relacionados con la base de datos. En `css` están los estilos de las pantallas y en `js` los archivos JavaScript que permiten realizar distintas interacciones.

También está la carpeta `imagenes`, donde guardé las imágenes, los íconos y el logo de Cursolandia, mientras que `uploads` se utiliza para los archivos que se suben desde la página.

Esta separación me permitió trabajar sobre una pantalla o una función sin tener que modificar todo el proyecto cada vez que necesitaba hacer algún cambio.

## Herramientas que utilicé

Para programar la página utilicé HTML, CSS, JavaScript, PHP, MySQL y Bootstrap.

Trabajé con Visual Studio Code para escribir y organizar el código. Para probar el funcionamiento desde mi computadora utilicé XAMPP, junto con phpMyAdmin para administrar la base de datos.

También utilicé Git y GitHub para guardar los cambios y tener un historial del desarrollo.

Una vez que la página estuvo funcionando localmente, la subí al servidor de alwaysdata para poder acceder a ella desde internet. Para transferir los archivos utilicé FileZilla.

## Qué se puede hacer en Cursolandia

Los usuarios pueden registrarse, completar su perfil, agregar sus antecedentes y seleccionar sus intereses. Esta información también se utiliza para recomendar cursos relacionados con los temas que les gustan.

Cada persona puede crear sus propios cursos o inscribirse en cursos de otros usuarios. Los cursos pueden ser públicos o privados, gratuitos o pagos, y sus creadores pueden establecer fechas, etiquetas y cupos.

Dentro de cada curso existe un espacio donde el creador puede compartir materiales y donde los participantes pueden hacer preguntas, responder consultas y conversar sobre los temas del curso.

La plataforma también cuenta con un Plan Gratuito y un Plan Pro. El Plan Gratuito tiene límites en la cantidad de cursos que una persona puede crear o realizar al mismo tiempo, mientras que el Plan Pro amplía esas posibilidades.

Para acceder al Plan Pro, el usuario puede enviar una solicitud con su comprobante. Después, el administrador puede revisarla, aprobarla o rechazarla y establecer la duración del plan.

## Problemas que encontré durante el desarrollo

Durante el desarrollo aparecieron algunos problemas que tuve que ir corrigiendo a medida que probaba las funciones.

Uno de ellos fue con los archivos CSS. En algunos momentos realizaba cambios en los estilos, pero al actualizar la página seguía apareciendo el diseño anterior. Para solucionarlo tuve que actualizar las versiones de los archivos CSS que estaban vinculados a las páginas, de manera que el navegador cargara los cambios nuevos.

También tuve problemas con la conexión a la base de datos y con algunas consultas que no devolvían la información que correspondía. Esto fue especialmente importante al trabajar con los usuarios, los cursos y las solicitudes.

Un ejemplo fue la gestión del Plan Pro. Al principio, cuando el administrador aprobaba una solicitud, el cambio no siempre se reflejaba correctamente en el perfil del usuario. Aunque la solicitud se había procesado, la información que se mostraba en pantalla no coincidía con el nuevo estado del plan.

Para corregirlo tuve que revisar cómo se guardaban los datos de la aprobación y cómo se consultaba el estado del plan desde el perfil. Después de realizar los cambios, probé nuevamente el recorrido completo, desde enviar una solicitud hasta aprobarla como administrador y comprobar que el perfil mostrara el Plan Pro activo junto con su fecha de vencimiento.

También aparecieron dificultades al pasar de XAMPP a alwaysdata, porque las rutas de los archivos y los datos de conexión a MySQL eran diferentes. Tuve que revisar esas configuraciones para que las mismas funciones pudieran ejecutarse correctamente desde el servidor.

Estas pruebas me sirvieron para detectar errores que no siempre se veían mientras programaba y para entender mejor cómo se relacionaban las diferentes partes del sistema.

## Instalación local

Para ejecutar Cursolandia localmente es necesario tener instalado XAMPP y contar con PHP y MySQL.

Primero hay que descargar el proyecto desde GitHub y colocar la carpeta `cursolandia` dentro de `htdocs`. Después se deben iniciar Apache y MySQL desde XAMPP.

Desde phpMyAdmin hay que crear una base de datos llamada `cursolandia` e importar el archivo SQL del proyecto. También es necesario configurar `bd/conexion.php` con los datos correspondientes a la conexión local.

Una vez hecho esto, se puede acceder a la página desde `http://localhost/cursolandia/`.

## Usuarios de prueba

Para realizar pruebas cargué 30 usuarios con diferentes perfiles, intereses y tipos de plan. Esto me permitió probar cómo se comportaba la página con distintas cuentas y comprobar las inscripciones, las recomendaciones y otras funciones relacionadas con los cursos.

También hay una cuenta administradora que permite gestionar las solicitudes del Plan Pro y consultar su historial.

**Pendiente:** agregar las credenciales de demostración para un usuario gratuito, uno Pro y un administrador.

## Acceso al proyecto

La aplicación está disponible en:

https://cursolandia.alwaysdata.net

**Repositorio GitHub:** pendiente de agregar el enlace.

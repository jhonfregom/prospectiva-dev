/**
 * -----------------------------------------------------------------------
 * Custom functions, include help methods or generales
 * Por Carlos Eduardo Mera Ruiz
 * -----------------------------------------------------------------------
 */

/**
* Función responsible to proccess the errores from request in ajax
* @param error error
* @return JSON {text,errors[]}
*/
export function procesarErroresRequest(error) {
    var text = '';
    var errors = [];
    if(Object.keys(error).length !== 0)
    {
        if (error.response)
        {
            switch(error.response.status)
            {
                case 0:
                    // text = 'Error en su conexión a internet';
                    text += error.response.data.error ? error.response.data.error : error.response.statusText;
                    break;
                case 422:
                    text += "Error:";
                    Object.keys(error.response.data.errors).forEach(function (key) {
                        if( Array.isArray(error.response.data.errors[key] ) )
                        {
                            error.response.data.errors[key].forEach(function (content) {
                                errors.push(content);
                            });
                        }else{
                            errors.push(error.response.data.errors[key]);
                        }

                    });
                    break;
                case 400:
                    text += error.response.data.error ? error.response.data.error : error.response.statusText;
                    Object.keys(error.response.data.data).forEach(function (key) {
                        if( Array.isArray(error.response.data.data[key] ) )
                        {
                            error.response.data.data[key].forEach(function (content) {
                                errors.push(content);
                            });
                        }else{
                            errors.push(error.response.data.data[key]);
                        }

                    });
                    break;
                // case 419:
                //     text = 'Excepción: Coincidencia de token CSRF';
                //     break;
                // case 404:
                //     text = 'Solicitud no encontrada';
                //     break;
                // case 405:
                //     text = 'Método no permitido';
                //     break;
                case 500:
                    text += error.response.data.error ? error.response.data.error : error.response.statusText;
                    // text = 'Error en su solicitud, por favor intentelo más tarde.';
                    errors.push(error.message);
                    if (error.response.data.message) {
                        errors.push(error.response.data.message);
                    }
                    break;
                // case 503:
                //     text = 'Problemas con su conexión a Internet, por favor intentelo más tarde.';
                //     break;
                default:
                    text += error.response.data.error ? error.response.data.error : error.response.statusText;
                    // if (response.statusText == "abort") {
                    //     text = 'Su solicitud ha sido abortada, por favor inténtelo más tarde.';
                    // } else {
                    //     text = 'Lo sentimos, error desconocido' + response.statusText + '';
                    // }
                    break;
            }
        } else if (error.request) {
            console.log("requestError", error.request);
            errors.push(error.request);
        } else {
            console.log('Error', error);
            errors.push(error);
        }
    }

    return {
        text: text,
        errors: errors
    };
}

/**
 * Function to capitalize a string
 * @param String word
 * @return String
 */
export function capitalize(word){
    if( word != undefined )
    {
        return word.replace(/^\w/, (c) => c.toUpperCase());
    }
}

/**
 * Function to capitalize each word in a string
 * @param String word
 * @return String
 */
export function capitalizeWords(word){
    if( word != undefined )
    {
        //Search each word and replace the first character to uppercase
        return word.replace(/\w\S*/g, (w) => (
            w.replace(/^\w/, (c) => c.toUpperCase())
        ));
    }
}

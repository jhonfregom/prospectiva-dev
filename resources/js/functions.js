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

                case 500:
                    text += error.response.data.error ? error.response.data.error : error.response.statusText;
                    
                    errors.push(error.message);
                    if (error.response.data.message) {
                        errors.push(error.response.data.message);
                    }
                    break;

                default:
                    text += error.response.data.error ? error.response.data.error : error.response.statusText;

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

export function capitalize(word){
    if( word != undefined )
    {
        return word.replace(/^\w/, (c) => c.toUpperCase());
    }
}

export function capitalizeWords(word){
    if( word != undefined )
    {
        
        return word.replace(/\w\S*/g, (w) => (
            w.replace(/^\w/, (c) => c.toUpperCase())
        ));
    }
}
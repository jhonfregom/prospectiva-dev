<script>
import { capitalize } from '../../../../js/functions';

//Import url from store
import { useUrlsStore } from '../../../stores/urls';

export default {
    setup(){
        const storeUrls = useUrlsStore();
        return { storeUrls };
    },
    props:{
        csrf_token: {
            type: String,
            required: true
        },
        urls_json: {
            type: String,
            required: true,
        },
        fields_json: {
            type: String,
            required: true
        },
        success_message: {
            type: String,
            default: null
        }
    },
    data() {
        return {
            user: '',
            password: '',
            fields: [],
            copy_msg_user: '',
            isCapsLock: false,
            showSuccessMessage: false,
            successMessage: ''
        }
    },
    created(){
        this.initUrlsStore();
        this.fields = JSON.parse(this.fields_json);
        this.copy_msg_user = this.fields.user.msg;
        
        if (this.success_message) {
            this.successMessage = this.success_message;
            this.showSuccessMessage = true;
            setTimeout(() => {
                this.showSuccessMessage = false;//############<-
            }, 3000); // El mensaje desaparecerá después de 3 segundos
        }
    },
    methods:{
        capitalize,
        initUrlsStore(){
            let urls = JSON.parse( this.urls_json );
            this.storeUrls.setUrls( urls );
        },
        clickLogin(e){
            this.fields.user.msg = this.copy_msg_user;
            this.fields.user.error = this.user === '';
            this.fields.password.error = this.password === '';

            if(  this.user !== '' || this.password !== '' )
            {
            // return true;
                const data = {
                    _token: this.csrf_token,
                    user: this.user,
                    password: this.password
                }
                axios.post( this.storeUrls.login, data)
                .then( res => {
                    if( res.data.status === 204 )
                    {
                        this.fields.user.msg = res.data.data[0];
                        this.fields.user.error = true;
                    }else if( res.data.status === 200 ){
                        window.location.href = this.storeUrls.home;
                    }
                });
            }

            e.preventDefault();
        },
        keyUpCapsLock(event)
        {
            if( event.getModifierState('CapsLock') )
            {
                this.isCapsLock = true;
            }else{
                this.isCapsLock = false;
            }
        },
    }
}
</script>
<template>
    <div>
        <div v-if="showSuccessMessage" class="notification is-success">
            {{ successMessage }}
        </div>
        <form
            :action="storeUrls.login"
            @submit="clickLogin"
            method="POST">
            <input type="hidden" name="_token" :value="csrf_token">
            <b-field
                v-bind:type="{ 'is-danger' : fields.user.error }"
                :message="fields.user.error ? fields.user.msg : ''">
                <b-input
                    name="user"
                    size="is-large"
                    type="text"
                    :placeholder="capitalize( fields.user.placeholder )"
                    autofocus=""
                    v-model="user" />
            </b-field>
            <b-field
                v-bind:type="{ 'is-danger' : fields.password.error }"
                :message="fields.password.error ? fields.password.msg : ''">
                <b-tooltip
                    :label="capitalize( fields.password.caps_lock )"
                    :active="isCapsLock"
                    position="is-bottom"
                    always>
                    <b-input
                        size="is-large"
                        type="password"
                        :placeholder="capitalize( fields.password.placeholder )"
                        autofocus=""
                        v-model="password"
                        v-on:keyup="keyUpCapsLock($event)"
                        v-on:blur="isCapsLock = isCapsLock ? false : isCapsLock"/>
                </b-tooltip>
            </b-field>
            <b-button name="password" class="is-block is-info" size="is-large is-fullwidth" native-type="submit">
                {{ fields.login.placeholder }}
            </b-button>
        </form>
    </div>
</template>

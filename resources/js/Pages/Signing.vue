<template>
    <Head :title="'Signing: '+title"></Head>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="max-w-xl w-full mx-auto p-6 lg:p-8">
            <div class="mt-6">
                <div class="dark:text-white/60">Title</div>
                <div class="text-xl font-semibold text-gray-900 dark:text-white">{{title}}</div>
            </div>
            <div class="mt-1 mb-5">
                <div class="dark:text-white/60">Subject</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{subject}}</div>
            </div>
            <div class="mt-1 mb-5">
                <div class="dark:text-white/60">Signer</div>
                <table class="w-full dark:text-white">
                    <tr>
                        <td>ID</td>
                        <td>{{signer.id}}</td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td>{{signer.name}}</td>
                    </tr>
                    <tr>
                        <td>Position</td>
                        <td>{{signer.position}}</td>
                    </tr>
                    <tr>
                        <td>Division</td>
                        <td>{{signer.division}}</td>
                    </tr>
                </table>
            </div>

            <div class="flex justify-evenly">
                <a :href="crm_link" class="bg-white/50 px-3 rounded-lg hover:bg-white" target="_blank">Open CRM</a>
                <a :href="document_link" class="bg-white/50 px-3 rounded-lg hover:bg-white" target="_blank">Download Document</a>
            </div>

            <div v-if="!success">
                <div class="text-gray-900 dark:text-white">Passcode</div>
                <div>
                    <input type="text" placeholder="Please input passcode" v-model="passcode" class="w-full rounded bg-white/20 dark:text-white">
                    <div v-if="error !== ''" class="text-red-500">{{error}}</div>
                    <div class="my-3 flex justify-end">
                        <button v-if="!loading" class="rounded bg-sky-500 text-sky-900 font-semibold hover:bg-gray-50 px-3 py-1" @click="confirm">CONFIRM and SIGN</button>
                        <button v-else class="rounded bg-sky-500 text-sky-900 font-semibold hover:bg-gray-50 px-3 py-1" disabled>LOADING...</button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="text-2xl dark:text-white p-3 bg-white/10 rounded-lg my-3">Signed Successfully</div>
            </div>
        </div>
    </div>
</template>

<script>
import {Head} from "@inertiajs/vue3";

export default {
    name: "Signing",
    components: {Head},
    props: {
        title: String,
        subject: String,
        crm_link: String,
        signer: Object,
        document_link: String
    },
    data(){
        return {
            passcode: '',
            loading: false,
            error: '',
            success: false
        };
    },
    methods:{
        confirm(){
            this.loading = true
            axios.post(location.href,{
                passcode: this.passcode
            }).then(res=>{
                if (res.status === 200){
                    this.success = true;
                }
            }).catch(e=>{
                if (e.response){
                    this.error = e.response?.data?.message;
                }
            }).finally(()=>{
                this.loading = false
            })
        }
    }
}
</script>

<style scoped>
</style>

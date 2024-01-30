<template>
    <Head :title="'Signing: '+title"></Head>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="max-w-lg w-full mx-auto p-6 lg:p-8">
            <div class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">SPA TITLE: {{title}}</div>
            <div v-if="!success">
                <div class="text-gray-900 dark:text-white">Passcode</div>
                <div>
                    <input type="text" placeholder="Please input passcode" v-model="passcode" class="w-full rounded bg-white/20 dark:text-white">
                    <div v-if="error !== ''" class="text-red-500">{{error}}</div>
                    <div class="my-3 flex justify-end">
                        <button v-if="!loading" class="rounded bg-sky-500 text-sky-900 font-semibold hover:bg-gray-50 px-3 py-1" @click="confirm">CONFIRM</button>
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
        title: String
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

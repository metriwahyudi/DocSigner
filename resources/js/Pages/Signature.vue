<template>
    <Head :title="filename"></Head>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="max-w-xl w-full mx-auto p-6 lg:p-8">
            <div class="text-lg font-semibold text-gray-900 dark:text-white mb-3">SID: {{sid}}</div>
            <div class="mt-1 mb-5">
                <div class="dark:text-white/60">Document</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{filename}}</div>
                <div class="flex justify-between">
                    <div class="dark:text-white">{{formatBytes(size)}}</div>
                    <a :href="document_link" class="bg-white/50 px-3 rounded-lg hover:bg-white" target="_blank">Download</a>
                </div>
            </div>
            <div class="mt-1 mb-5">
                <div class="dark:text-white/60">Hash (SHA-1)</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{hash}}</div>
            </div>
            <div class="mt-1 mb-5">
                <div class="dark:text-white/60">Signed by</div>
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
            <div class="mt-1 mb-5">
                <div class="dark:text-white/60">Signed at</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">{{time}}</div>
            </div>
        </div>
    </div>
</template>

<script>
import {Head} from "@inertiajs/vue3";

export default {
    name: "Signature",
    components: {Head},
    props:{
        filename: '',
        hash:'',
        signer: Object,
        time: '',
        document_link: '',
        sid: '',
        size:''
    },
    methods:{
        formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
        }
    }
}
</script>

<style scoped>

</style>

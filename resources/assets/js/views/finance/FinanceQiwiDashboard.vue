<template>
    <div>
        <vue-progress-bar></vue-progress-bar>
        <page-header icon="fa-money" title="Панель управления">
            <li>
                <a class="disabled">Финансы</a>
            </li>
            <router-link tag="li" to="/finance/qiwi">
                <a>Qiwi Visa Wallet</a>
            </router-link>
        </page-header>

        <loading :show="!walletsIsLoaded"></loading>

        <div v-if="walletsIsLoaded" class="container-fluid">
            <div class="form-inline">
                <h3 class="body-header">Управление кошельками</h3>

                <div class="form-group m-b-none">
                    <select name="" id="" class="form-control"
                            v-model="massAction">
                        <option v-for="(text, action) in actions"
                                :value=action>
                            {{ text }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <button class="btn btn-default"
                            :disabled="!selected.length"
                            @click="executeMassAction">
                        Выполнить
                    </button>
                </div>
            </div>

            <br>
            <div class="form-group">
                <button class="btn btn-default"
                        :disabled="!selected.length"
                        @click="updateWallets">
                    Обновить выбранные кошельки
                </button>
            </div>

            <br>
            <div class="m-b-lg">
                <router-link to="/finance/qiwi/add-wallet" class="btn btn-success">
                    <i class="fa fa-plus-square fa-btn"></i>Добавить кошелёк
                </router-link>
            </div>

            <div class="input-group m-b-lg">
                <input type="text" placeholder="" class="form-control" v-model="searchQuery">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default">Поиск</button>
                </span>
            </div>

            <transition name="fade">

            </transition>


            <qiwi-type-panel
                    v-for="type in walletsTypes"
                    :key="type.id"
                    :type="type"
                    :types="walletsTypes"
                    @updateSelected="updateSelected"
                    @moveWallets="moveWallets">
            </qiwi-type-panel>

            <qiwi-type-panel
                    v-if="inactive.wallets.length>0"
                    :type="inactive"
                    :types="walletsTypes"
                    :exclude="'spent'"
                    :is-inactive="true"
                    @moveWallets="moveWallets">
            </qiwi-type-panel>
        </div>
    </div>

</template>

<script>
    import QiwiTypePanel from './qiwi/type/QiwiTypePanel.vue';
    import 'babel-polyfill';

    export default {
        components: {QiwiTypePanel},
        mounted() {
//            console.log(window.Dinero);
            Vue.ls.set('actions', this.actions);
            this.fetchWallets();
            this.runningLine();
        },

        beforeDestroy() {
            // stop the running line
            this.updatingWalletsRoutine = false;
        },

        data() {
            return {
                actions: {
                    moveToReceive: "Переместить в приемные",
                    moveToWithdraw: "Переместить в выводные",
                    moveToAutoWithdrawNumber: "Переместить в автовыводные\\номер",
                    moveToAutoWithdrawCard: "Переместить в автовыводные\\карта",
                    moveToReserve: "Переместить в резервные",
                    moveToSpent: "Переместить в отработанные",
                    remove: "Удалить"
                },
                massAction: "",
                searchQuery: "",
                walletsIsLoaded: false,
                walletsTypes: null,
                selected: [],
                updatingWalletsRoutine: true,
                updateDelay: 10,
            };
        },

        watch: {
            filter() {

            }
        },

        methods: {
            updateSelected(selected) {
                if (selected.length === 0) return;

                let typeId = selected[0].type_id;
                let walletsWithoutThisType = this.selected.filter((wallet) => wallet.type_id !== typeId);
                this.selected = walletsWithoutThisType.concat(selected);
            },

            updateWallets() {
                this.$emit('update', this.selected.map((wallet) => wallet.login));
                this.selected = [];
            },

            fetchWallets() {
                this.$Progress.start();
                axios.get('/api/qiwi-wallets')
                    .then((response) => {
//                            console.log(response);
                        this.walletsTypes = response.data;
                        this.walletsIsLoaded = true;

                        this.$Progress.finish();
                    });
                Bus.$emit('initTooltip');
            },

            // make line on top appear every X seconds and update all wallets from the database
            async runningLine() {
                while (true) {
                    await this.sleep(this.updateDelay * 1000);
                    if (!this.updatingWalletsRoutine) break;

                    this.fetchWallets();
                }
            },

            sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            },

            moveWallets(wallets, fromId, toId) {
                // convert array of wallets entities to array ids
                let ids = wallets.map((wallet) => wallet.id);
                axios.post('/api/qiwi-wallets/move', {wallets: ids, to: toId})
                    .then(() => {
                        let moveTo = this.walletsTypes.find(type => type.id === toId);

                        let moveFrom = this.walletsTypes.find(type => type.id === fromId);

                        moveFrom.wallets = moveFrom.wallets.filter((w) => {
                            return !wallets.find(item => item.id === w.id);
                        });

                        wallets.forEach((w) => {
                            w.is_active = 1;
                            moveTo.wallets.push(w)
                        });
                    });
            },

            executeMassAction() {
                Vue.ls.set('wallets', this.selected);
                Vue.ls.set('action', this.massAction);

                this.$router.push({path: `/finance/qiwi/mass-action`});
            },
        },

        computed: {
            inactive() {
                let inactive = {name: 'Неактивные киви', wallets: [], selected: []};

                this.walletsTypes.forEach((type) => {
                    type.wallets.forEach((w) => {
                        if (!w.is_active) {
                            inactive.wallets.push(w);
                        }
                    });
                });

                return inactive;
            },
        },
    };
</script>
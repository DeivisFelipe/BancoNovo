<template>
  <v-container class="dashboard-container py-8">
    <v-row>
      <!-- Aside - Informações da Conta -->
      <v-col cols="12" md="3">
        <v-card elevation="8" class="account-card pa-6">
          <div class="text-center mb-6">
            <v-avatar color="primary" size="80" class="mb-4">
              <span class="text-h4 font-weight-bold">{{ userInitials }}</span>
            </v-avatar>
            <h2 class="text-h5 font-weight-bold mb-2">{{ user.name }}</h2>
            <p class="text-body-2 text-medium-emphasis mb-1">{{ user.email }}</p>
            <v-chip color="primary" variant="flat" class="mt-2">
              <v-icon start>mdi-account</v-icon>
              Conta: {{ user.account_number }}
            </v-chip>
          </div>

          <v-divider class="my-4"></v-divider>

          <v-list density="compact" class="bg-transparent">
            <v-list-item>
              <template v-slot:prepend>
                <v-icon color="success">mdi-wallet</v-icon>
              </template>
              <v-list-item-title class="font-weight-medium">Saldo</v-list-item-title>
              <v-list-item-subtitle class="text-h6 text-success">
                {{ formatCurrency(balance) }}
              </v-list-item-subtitle>
            </v-list-item>
          </v-list>

          <v-divider class="my-4"></v-divider>

          <v-btn color="error" variant="flat" block prepend-icon="mdi-logout" @click="logout">
            Sair da Conta
          </v-btn>
        </v-card>
      </v-col>

      <!-- Main - Saldo e Transações -->
      <v-col cols="12" md="9">
        <!-- Saldo e Ações -->
        <v-card elevation="8" class="mb-4">
          <v-card-text class="pa-6">
            <v-row align="center">
              <v-col cols="12" md="6">
                <div class="text-body-1 text-medium-emphasis mb-2">Saldo Disponível</div>
                <div class="text-h3 font-weight-bold text-primary">
                  {{ formatCurrency(balance) }}
                </div>
              </v-col>
              <v-col cols="12" md="6" class="text-right">
                <v-btn color="primary" size="large" class="mr-2" prepend-icon="mdi-cash-plus" @click="openDepositDialog">
                  Depositar
                </v-btn>
                <v-btn color="success" size="large" class="mr-2" prepend-icon="mdi-bank-transfer" @click="openTransferDialog">
                  Transferir
                </v-btn>
                <v-btn color="grey" size="large" variant="outlined" icon="mdi-refresh" @click="refreshData" :loading="refreshing"></v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Transações -->
        <v-card elevation="8">
          <v-card-title class="pa-4 d-flex align-center">
            <v-icon class="mr-2">mdi-history</v-icon>
            <span class="text-h6">Transações Recentes</span>
          </v-card-title>
          <v-divider></v-divider>

          <div class="transactions-container">
            <v-list v-if="transactions.length > 0" class="pa-0">
              <template v-for="(transaction, index) in transactions" :key="index">
                <v-list-item class="px-4 py-3">
                  <template v-slot:prepend>
                    <v-avatar :color="transaction.type === 'received' ? 'success' : 'error'" size="40">
                      <v-icon color="white">
                        {{ transaction.type === 'received' ? 'mdi-arrow-down' : 'mdi-arrow-up' }}
                      </v-icon>
                    </v-avatar>
                  </template>

                  <v-list-item-title class="font-weight-medium">
                    {{ transaction.type === 'received' ? 'Recebido de' : 'Enviado para' }} {{ transaction.name }}
                  </v-list-item-title>
                  <v-list-item-subtitle class="text-caption">
                    Conta: {{ transaction.account }} • {{ transaction.date }}
                  </v-list-item-subtitle>

                  <template v-slot:append>
                    <div class="text-h6 font-weight-bold" :class="transaction.type === 'received' ? 'text-success' : 'text-error'">
                      {{ transaction.type === 'received' ? '+' : '-' }} {{ formatCurrency(transaction.amount) }}
                    </div>
                  </template>
                </v-list-item>
                <v-divider v-if="index < transactions.length - 1"></v-divider>
              </template>
            </v-list>

            <div v-else class="text-center py-12">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-inbox</v-icon>
              <p class="text-body-1 text-medium-emphasis">Nenhuma transação realizada ainda</p>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Modal de Nova Transferência -->
    <v-dialog v-model="transferDialog" max-width="600">
      <v-card>
        <v-card-title class="pa-4 bg-primary">
          <v-icon class="mr-2" color="white">mdi-bank-transfer</v-icon>
          <span class="text-white">Nova Transferência</span>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-6">
          <v-form @submit.prevent="submitTransfer">
            <v-autocomplete v-model="selectedRecipient" v-model:search="searchQuery" :items="usersList" :loading="searchLoading" item-title="label" item-value="id" label="Buscar destinatário" variant="outlined" prepend-inner-icon="mdi-account-search" class="mb-4" no-filter clearable hint="Digite o nome ou número da conta" persistent-hint>
              <template v-slot:item="{ props, item }">
                <v-list-item v-bind="props">
                  <template v-slot:prepend>
                    <v-avatar color="primary" size="40">
                      <span class="text-caption">{{ getInitials(item.raw.name) }}</span>
                    </v-avatar>
                  </template>
                  <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                  <v-list-item-subtitle>Conta: {{ item.raw.account_number }}</v-list-item-subtitle>
                </v-list-item>
              </template>
              <template v-slot:no-data>
                <v-list-item>
                  <v-list-item-title>
                    {{ searchQuery.length < 2 ? 'Digite pelo menos 2 caracteres' : 'Nenhum usuário encontrado' }}
                  </v-list-item-title>
                </v-list-item>
              </template>
            </v-autocomplete>

            <v-card v-if="selectedRecipientData" variant="outlined" class="mb-4 pa-3">
              <div class="d-flex align-center">
                <v-avatar color="primary" size="40" class="mr-3">
                  <span class="text-caption">{{ getInitials(selectedRecipientData.name) }}</span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ selectedRecipientData.name }}</div>
                  <div class="text-caption text-medium-emphasis">Conta: {{ selectedRecipientData.account_number }}</div>
                </div>
              </div>
            </v-card>

            <v-text-field label="Valor" variant="outlined" prepend-inner-icon="mdi-currency-usd" type="number" step="0.01" v-model="transferForm.amount" hint="Digite o valor a ser transferido" persistent-hint class="mb-4" />

            <v-alert v-if="transferForm.amount && futureBalance >= 0" type="info" variant="tonal" class="mb-0">
              <strong>Saldo após transferência:</strong> {{ formatCurrency(futureBalance) }}
            </v-alert>

            <v-alert v-if="transferForm.amount && futureBalance < 0" type="error" variant="tonal" class="mb-0">
              <strong>Saldo insuficiente!</strong> Você não possui saldo para esta transferência.
            </v-alert>
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="closeTransferDialog">
            Cancelar
          </v-btn>
          <v-btn color="primary" variant="flat" @click="submitTransfer" :disabled="!selectedRecipient || !transferForm.amount || futureBalance < 0">
            Transferir
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Modal de Depósito -->
    <v-dialog v-model="depositDialog" max-width="500">
      <v-card>
        <v-card-title class="pa-4 bg-primary">
          <v-icon class="mr-2" color="white">mdi-cash-plus</v-icon>
          <span class="text-white">Fazer Depósito</span>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pa-6">
          <v-form @submit.prevent="submitDeposit">
            <v-text-field label="Valor do depósito" variant="outlined" prepend-inner-icon="mdi-currency-usd" type="number" step="0.01" v-model="depositForm.amount" hint="Digite o valor que deseja depositar" persistent-hint autofocus />
          </v-form>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="grey" variant="text" @click="closeDepositDialog">
            Cancelar
          </v-btn>
          <v-btn color="primary" variant="flat" @click="submitDeposit" :disabled="!depositForm.amount">
            Depositar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  user: Object,
  balance: Number,
  transactions: Array,
});

// Iniciais do usuário
const userInitials = computed(() => {
  const names = props.user.name.split(" ");
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase();
  }
  return names[0][0].toUpperCase();
});

// Função para obter iniciais de qualquer nome
const getInitials = (name) => {
  const names = name.split(" ");
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase();
  }
  return names[0][0].toUpperCase();
};

// Estado de refresh
const refreshing = ref(false);

// Modal de transferência
const transferDialog = ref(false);
const depositDialog = ref(false);
const transferForm = ref({
  recipient_id: null,
  amount: null,
});
const depositForm = ref({
  amount: null,
});

// Autocomplete
const searchQuery = ref("");
const searchLoading = ref(false);
const usersList = ref([]);
const selectedRecipient = ref(null);
const selectedRecipientData = ref(null);

// Debounce para busca
let searchTimeout = null;
watch(searchQuery, (newValue) => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  if (newValue && newValue.length >= 2) {
    searchLoading.value = true;
    searchTimeout = setTimeout(async () => {
      try {
        const response = await axios.get("/search-users", {
          params: { search: newValue },
        });
        usersList.value = response.data;
      } catch (error) {
        console.error("Erro ao buscar usuários:", error);
        usersList.value = [];
      } finally {
        searchLoading.value = false;
      }
    }, 300);
  } else {
    usersList.value = [];
  }
});

// Watch para atualizar dados do destinatário selecionado
watch(selectedRecipient, (newValue) => {
  if (newValue) {
    const user = usersList.value.find((u) => u.id === newValue);
    selectedRecipientData.value = user || null;
    transferForm.value.recipient_id = newValue;
  } else {
    selectedRecipientData.value = null;
    transferForm.value.recipient_id = null;
  }
});

const openTransferDialog = () => {
  transferDialog.value = true;
};

const closeTransferDialog = () => {
  transferDialog.value = false;
  transferForm.value = {
    recipient_id: null,
    amount: null,
  };
  selectedRecipient.value = null;
  selectedRecipientData.value = null;
  searchQuery.value = "";
  usersList.value = [];
};

const openDepositDialog = () => {
  depositDialog.value = true;
};

const closeDepositDialog = () => {
  depositDialog.value = false;
  depositForm.value = {
    amount: null,
  };
};

const submitTransfer = () => {
  router.post("/transfer", transferForm.value, {
    onSuccess: () => closeTransferDialog(),
    preserveScroll: true,
  });
};

const submitDeposit = () => {
  router.post("/deposit", depositForm.value, {
    onSuccess: () => closeDepositDialog(),
    preserveScroll: true,
  });
};

const refreshData = () => {
  refreshing.value = true;
  router.reload({ only: ["balance", "transactions"] });
  setTimeout(() => {
    refreshing.value = false;
  }, 1000);
};

const logout = () => {
  router.post("/logout");
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value);
};

// Saldo futuro (previsão)
const futureBalance = computed(() => {
  if (transferForm.value.amount) {
    return props.balance - parseFloat(transferForm.value.amount);
  }
  return props.balance;
});
</script>

<style scoped>
.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
}

.account-card {
  position: sticky;
  top: 20px;
}

.transactions-container {
  max-height: 600px;
  overflow-y: auto;
}

/* Personalizar scrollbar */
.transactions-container::-webkit-scrollbar {
  width: 8px;
}

.transactions-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.transactions-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.transactions-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>

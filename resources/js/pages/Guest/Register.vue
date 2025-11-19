<template>
  <v-card elevation="8" class="rounded-lg">
    <v-card-title class="text-h4 text-center pa-6">
      REGISTRAR
    </v-card-title>
    <v-card-text class="pa-6">
      <v-form @submit.prevent="submit">
        <v-text-field label="Nome" variant="outlined" prepend-inner-icon="mdi-account" class="mb-2" v-model="form.name" :error-messages="form.errors.name" />
        <v-text-field label="Email" type="email" variant="outlined" prepend-inner-icon="mdi-email" class="mb-2" v-model="form.email" :error-messages="form.errors.email" />
        <v-text-field label="Senha" type="password" variant="outlined" prepend-inner-icon="mdi-lock" class="mb-2" v-model="form.password" :error-messages="form.errors.password" />
        <v-text-field label="Confirmar Senha" type="password" variant="outlined" prepend-inner-icon="mdi-lock" class="mb-2" v-model="form.password_confirmation" />
        <v-btn color="primary" block size="large" class="mt-4 mb-4" type="submit" :loading="form.processing">
          Criar Conta
        </v-btn>

        <div class="text-center my-4">
          <div class="d-flex align-center">
            <v-divider></v-divider>
            <span class="mx-3 text-caption text-medium-emphasis">ou</span>
            <v-divider></v-divider>
          </div>
        </div>

        <div class="text-center">
          <span class="text-body-2">Já tem uma conta? </span>
          <a href="/login" class="text-primary text-decoration-none font-weight-medium cursor-pointer" @click.prevent="$inertia.visit('/login')">
            Fazer login
          </a>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import Swal from "sweetalert2";

const form = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post("/register", {
    onError: (errors) => {
      const errorMessages = Object.values(errors).join("\n");
      Swal.fire({
        icon: "error",
        title: "Erro ao criar conta",
        text: errorMessages,
        confirmButtonColor: "#1976D2",
      });
    },
  });
};
</script>

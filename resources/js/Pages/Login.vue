<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="form-block">
                        <div class="mb-4">
                            <h2>
                                Sign in to your account
                            </h2>
                        </div>
                        <form action="#" method="POST" @submit.prevent="login">
                            <div class="form-group first">
                              <label for="email">Email</label>
                              <input type="email" class="form-control" id="email" v-model="loginForm.email" autocomplete="email" required>
                            </div>
                            <div class="form-group last mb-4">
                              <label for="password">Password</label>
                              <input class="form-control" v-model="loginForm.password" id="password" name="password" type="password"
                              autocomplete="current-password" required>
                            </div>
                            <button type="submit" class="btn btn-pill text-white btn-block btn-primary">Log In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { reactive, onMounted, watch } from "vue";
    import { useRouter, NavigationFailureType, isNavigationFailure } from "vue-router";
    import API from "../services/API";
    import TokenService from "../services/TokenService"
    const router = useRouter();

    onMounted(() => {
        console.log("login component created");
        tokenCheck();
    });

    const tokenCheck = () => {
        if (TokenService.getAccessToken()) {
            router.push({ name: 'Chat' });
        } else {
            console.log("token not found");
        }
    };

    const loginForm = reactive({
        email: "",
        password: "",
    });

    const login = async () => {
        try {
            const response = await API.post("login", loginForm);
            console.log(response.data);
            TokenService.setToken(response.data.token)
            tokenCheck()
        }catch(e) {
            console.error(e);
        }
    }
</script>
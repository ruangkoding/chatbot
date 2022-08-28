import { createRouter, createWebHistory } from "vue-router";
import TokenService from "./services/TokenService";

const Login = () => import("./Pages/Login.vue");
const Chat = () => import("./Pages/Chat.vue");

const routes = [
    {
        path: "/",
        name: "Login",
        component: Login,
    },
    {
        path: "/chat",
        name: "Chat",
        component: Chat,
        meta: {
            requiresAuth: true,
        },
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return (
            savedPosition ||
            new Promise((resolve) => {
                setTimeout(
                    () =>
                        resolve({
                            top: 0,
                            behavior: "smooth",
                        }),
                    300
                );
            })
        );
    },
});

router.beforeEach((to, from) => {
    if (to.meta.requiresAuth && !TokenService.getAccessToken()) {
        return {
            name: "Login",
            query: {
                redirect: to.fullPath,
            },
        };
    }
});

export default router;
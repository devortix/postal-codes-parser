<template>
  <div class="common-layout">
    <el-container>
      <el-header class="header">
        <el-form :inline="true">
          <el-form-item label="Поштовий індекс" prop="region">
            <el-input v-model="searcCode" maxlength="30"></el-input>
          </el-form-item>

          <el-form-item label="Адреса" prop="district">
            <el-input v-model="searcAddress" maxlength="30"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click.prevent="search">Пошук</el-button>
          </el-form-item>

        </el-form>

        <el-form style="margin-left: auto">
          <el-form-item>
            <el-button type="success" @click="openCreateDialog">Створити</el-button>
          </el-form-item>
        </el-form>
      </el-header>
      <el-main>

        <!-- Список постів -->
        <el-row :gutter="20">
          <el-col :xs="12" :sm="6" :md="6" v-for="(post, index) in posts" :key="index">
            <div class="post">
              <h3 style="margin: 0">{{ post.code }}</h3>
              <div>{{ post.region }}</div>
              <div>{{ post.city }}</div>
              <div>
                <el-button :icon="Document" circle @click="openDetailsDialog(post)" />

                <el-button type="danger" :icon="Delete" circle @click="deletePost(post.code)" />
              </div>
            </div>

            <!-- Іконка "Деталі" і кнопка "Видалити" -->

          </el-col>
        </el-row>

        <!-- Попап з деталями -->
        <el-dialog v-model="detailsDialogVisible" :title="detailsPost?.code ?? ''" width="500"
          style="text-align: center;">
          <table style="text-align: left; width: 100%;">
            <tr>
              <td><b>Область:</b> </td>
              <td>
                <span>{{ detailsPost.region }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Район:</b> </td>
              <td>
                <span>{{ detailsPost.district }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Населений пункт:</b> </td>
              <td>
                <span>{{ detailsPost.city }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Region (Oblast):</b> </td>
              <td>
                <span>{{ detailsPost.region_slug }}</span>
              </td>
            </tr>
            <tr>
              <td><b>District new (Raion new):</b> </td>
              <td>
                <span>{{ detailsPost.district_slug }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Settlement:</b> </td>
              <td>
                <span>{{ detailsPost.city_slug }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Вiддiлення зв`язку:</b> </td>
              <td>
                <span>{{ detailsPost.office_name }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Post office:</b> </td>
              <td>
                <span>{{ detailsPost.office_slug }}</span>
              </td>
            </tr>
            <tr>
              <td><b>Поштовий індекс відділення зв`язку (Post code of post office):</b> </td>
              <td>
                <span>{{ detailsPost.office_slug }}</span>
              </td>
            </tr>
          </table>
          <span slot="footer" class="dialog-footer">
            <el-button @click="detailsDialogVisible = false">Закрити</el-button>
          </span>
        </el-dialog>

        <!-- Попап для створення нового поста -->
        <el-dialog v-model="createDialogVisible" title="Створити пост">
          <el-form :model="newPost" ref="createForm" :rules="rules" label-width="200px">
            <el-form-item label="Поштовий індекс" prop="code">
              <el-input v-model="newPost.code" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Регіон" prop="region">
              <el-input v-model="newPost.region" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Район" prop="district">
              <el-input v-model="newPost.district" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Місто" prop="city">
              <el-input v-model="newPost.city" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Region (Oblast)" prop="region_slug">
              <el-input v-model="newPost.region_slug" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="District new (Raion new)" prop="district_slug">
              <el-input v-model="newPost.district_slug" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Settlement" prop="city_slug">
              <el-input v-model="newPost.city_slug" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Вiддiлення зв`язку" prop="office_name">
              <el-input v-model="newPost.office_name" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Post office" prop="office_slug">
              <el-input v-model="newPost.office_slug" maxlength="30"></el-input>
            </el-form-item>

            <el-form-item label="Індекс відділення" prop="office_code">
              <el-input v-model="newPost.office_code" maxlength="30"></el-input>
            </el-form-item>
          </el-form>

          <span slot="footer" class="dialog-footer">
            <el-button @click="createDialogVisible = false">Закрити</el-button>
            <el-button type="primary" @click="createPost">Створити</el-button>
          </span>
        </el-dialog>

        <!-- Пагінація -->
        <el-pagination background :current-page="filterForm.page" :page-size="pageSize" :total="totalPosts"
          layout="prev, pager, next" @current-change="handlePageChange" />
      </el-main>
    </el-container>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { ElButton, ElDialog, ElForm, ElFormItem, ElInput, ElPagination, ElContainer, ElHeader, ElMain, ElNotification } from 'element-plus';
import {
  Delete,
  Document,
} from '@element-plus/icons-vue'
import axios from 'axios';

// Дані постів
const posts = ref([]);
const totalPosts = ref(posts.value.length);
const pageSize = ref(10);
const searcCode = ref('');
const searcAddress = ref('');
const filterForm = ref({
  code: '',
  address: '',
  page: 1
})

const fetchPosts = async (page = 1) => {
  try {
    filterForm.value.page = page;
    console.log(filterForm.value);
    const response = await axios.get(import.meta.env.VITE_API_URL + '?' + (new URLSearchParams(filterForm.value)).toString()).catch(() => {
        showError('Помилка запиту')
      });
    posts.value = response.data?.items || [];
    filterForm.value.page = response.data?.pagination?.currentPage || 1;
    pageSize.value = response.data?.pagination?.limit || 50;
    totalPosts.value = response.data?.pagination?.totalItems || 0;
  } catch (error) {
    console.error('Помилка при отриманні даних:', error);
  }
};

// перший запит до каталогу
fetchPosts()

// Для попапів
const detailsDialogVisible = ref(false);
const createDialogVisible = ref(false);
const detailsPost = ref(null);
const createForm = ref()
// Дані для нових постів
const newPost = ref({
  code: '',
  region: '',
  district: '',
  city: '',
  region_slug: '',
  district_slug: '',
  city_slug: '',
  office_name: '',
  office_slug: '',
  office_code: ''
});

const rules = []

for (const key in newPost.value) {
  rules[key] = [{ required: true, message: 'Поле обов’язкове до заповнення', trigger: 'blur' }];
}

// Відкриває попап з деталями
const openDetailsDialog = (post) => {
  detailsPost.value = post;
  detailsDialogVisible.value = true;
};

// Відкриває попап для створення нового поста
const openCreateDialog = () => {
  createDialogVisible.value = true;
};

// Створює новий пост
const createPost = () => {
  createForm.value.validate(async (valid) => {
    if (valid) {
      axios.post(import.meta.env.VITE_API_URL, newPost.value).then(() => {
        showSuccess('Створено')
        fetchPosts()
      }).catch(() => {
        showError('Помилка створення поштового індексу')
      })
      createDialogVisible.value = false;
     } else {
      console.log('Форма не дійсна!');
      return false;
    }
  });
};

watch(() => createDialogVisible, () => newPost.value = {});

// Видаляє пост
const deletePost = (code) => {
    axios.delete(import.meta.env.VITE_API_URL + code).then(() => {
        showSuccess('Видалено')
        fetchPosts()
      }).catch(() => {
        showError('Помилка видалення поштового індексу')
      })
 };

// Пошук
 const search = () => {
  filterForm.value.code = searcCode.value;
  filterForm.value.address = searcAddress.value;

  fetchPosts()
 }

// Обробляє зміну сторінки
const handlePageChange = (newPage) => {
  fetchPosts(newPage)
  console.log(`Нова сторінка: ${newPage}`);
};

// Нотіфікації
const showError = (msg) => {
  ElNotification({
    title: 'Error',
    message: msg,
    type: 'error',
  })
}

const showSuccess = (msg = 'Виконано') => {
  ElNotification({
    title: 'Success',
    message: msg,
    type: 'success',
  })
}
</script>
<style>
.post {
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: #6d8aa888;
  display: flex;
  flex-direction: column;
  gap: 5px;
  align-items: center;
  padding: 10px 4px;
  margin-bottom: 20px;
}


.el-dialog__footer {
  display: flex;
  justify-content: flex-end;
}

:root {
  --el-color-primary: #296fb5ab;
  /* Основной цвет */
  --el-bg-color-base: #f0f2f5;
  /* Цвет фона */
  --el-text-color: #333;
  /* Цвет текста */
}

.header {
  background-color: #213d5b;
  ;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
}

.header form .el-form-item {
  margin-bottom: 0;
}

.header label {
  color: white;

}

table td {
  vertical-align: top;
  
}
table td:last-child {
  width: 100px;
}
</style>
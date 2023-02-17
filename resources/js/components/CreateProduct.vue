<template>
    <section>
        <div class="row">
            <div v-if="errors" class="col-md-12 text-danger">
                <div class="card shadow mb-4">
                    {{ errors }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="">Product Name</label>
                            <input type="text" v-model="product_name" placeholder="Product Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Product SKU</label>
                            <input type="text" v-model="product_sku" placeholder="Product Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Description</label>
                            <textarea v-model="description" id="" cols="30" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                    </div>
                    <div class="card-body border">
                        <vue-dropzone
                            ref="myVueDropzone"
                            id="dropzone"
                            :options="dropzoneOptions"
                            @vdropzone-success="imageUploadSuccess"
                            @vdropzone-removed-file="removedFile"
                        />
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                    </div>
                    <div class="card-body">
                        <div class="row" v-for="(item,index) in product_variant">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Option</label>
                                    <select v-model="item.option" class="form-control">
                                        <option v-for="variant in variants"
                                                :value="variant.id">
                                            {{ variant.title }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label v-if="product_variant.length != 1" @click="product_variant.splice(index,1); checkVariant"
                                           class="float-right text-primary"
                                           style="cursor: pointer;">Remove</label>
                                    <label v-else for="">.</label>
                                    <input-tag v-model="item.tags" @input="checkVariant" class="form-control"></input-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" v-if="product_variant.length < variants.length && product_variant.length < 3">
                        <button @click="newVariant" class="btn btn-primary">Add another option</button>
                    </div>

                    <div class="card-header text-uppercase">Preview</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <td>Variant</td>
                                    <td>Price</td>
                                    <td>Stock</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="variant_price in product_variant_prices">
                                    <td>{{ variant_price.title }}</td>
                                    <td>
                                        <input type="text" class="form-control" v-model="variant_price.price">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" v-model="variant_price.stock">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button v-if="editMode" @click="update" type="submit" class="btn btn-lg btn-primary">Update</button>
        <button v-else @click="store" type="submit" class="btn btn-lg btn-primary">Save</button>
        <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
    </section>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import InputTag from 'vue-input-tag'

export default {
    components: {
        vueDropzone: vue2Dropzone,
        InputTag
    },
    props: {
        variants: {
            type: Array,
            required: true
        },
        product: {
            type: Object,
            required: false,
            default: () => {
            }
        }
    },
    data() {
        return {
            product_id: null,
            product_name: '',
            product_sku: '',
            description: '',
            images: [],
            editMode: false,
            product_variant: [
                {
                    option: this.variants[0].id,
                    tags: []
                }
            ],
            product_variant_prices: [],
            dropzoneOptions: {
                url: '/upload-photo',
                thumbnailWidth: 150,
                maxFilesize: 4,
                headers: {
                    'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                },
                addRemoveLinks: true
            },
            errors: null
        }
    },
    methods: {
        init() {
            if (this.product && Object.keys(this.product).length) {
                this.editMode = true
                this.product_id = this.product?.id || null
                this.product_name = this.product?.title || ''
                this.product_sku = this.product?.sku || ''
                this.description = this.product?.description || ''
                this.images = [] = this.product.product_images.map(el => el.file_path)

                let all_variants = this.variants.map(el => el.id)
                let selected_variants = this.product_variant.map(el => el.option);
                let available_variants = all_variants.filter(entry1 => !selected_variants.some(entry2 => entry1 == entry2))

                const product_variants = this.product?.product_variants || []
                let data = []
                let v_ids = []

                // Adding Product Variants
                product_variants.forEach((obj) => {
                    if (v_ids.includes(obj.variant_id)) {
                        data.filter(y => {
                            if (y.option === obj.variant_id) y.tags.push(obj.variant)
                        })
                    } else {
                        data.push({option: obj.variant_id, tags: [obj.variant]})
                        v_ids.push(obj.variant_id)
                    }
                })
                this.product_variant = data

                const product_variant_prices = this.product?.product_variant_prices || []
                product_variant_prices.forEach(obj => {
                    let title = product_variants.find(z => z.id === obj.product_variant_one)?.variant
                    if (obj.product_variant_two) title += '/' + product_variants.find(z => z.id === obj.product_variant_one)?.variant
                    if (obj.product_variant_three) title += '/' + product_variants.find(z => z.id === obj.product_variant_one)?.variant
                    title += '/';
                    this.product_variant_prices.push({title: title, price: obj.price, stock: obj.stock})
                })
            }
        },
        imageUploadSuccess(file, response) {
            this.images.push(response.file_path)
        },
        removedFile(file, error, xhr) {
            const index = this.images.indexOf(file.name)
            if (index > -1) this.images.splice(index, 1)
        },
        newVariant() {
            let all_variants = this.variants.map(el => el.id)
            let selected_variants = this.product_variant.map(el => el.option);
            let available_variants = all_variants.filter(entry1 => !selected_variants.some(entry2 => entry1 == entry2))

            this.product_variant.push({
                option: available_variants[0],
                tags: []
            })
        },

        // check the variant and render all the combination
        checkVariant() {
            let tags = [];
            this.product_variant_prices = [];
            this.product_variant.filter((item) => {
                tags.push(item.tags);
            })

            this.getCombn(tags).forEach(item => {
                this.product_variant_prices.push({
                    title: item,
                    price: 0,
                    stock: 0
                })
            })
        },
        getCombn(arr, pre) {
            pre = pre || '';
            if (!arr.length) {
                return pre;
            }
            let self = this;
            let ans = arr[0].reduce(function (ans, value) {
                return ans.concat(self.getCombn(arr.slice(1), pre + value + '/'));
            }, []);
            return ans;
        },
        store() {
            let payload = {
                title: this.product_name,
                sku: this.product_sku,
                description: this.description,
                product_images: this.images,
                product_variants: this.product_variant,
                product_variant_prices: this.product_variant_prices
            }

            axios.post('/product', payload).then(response => {
                this.clearForm()
                console.log(response.data);
            }).catch(error => {
                this.errors = error
            })
        },
        update() {
            let payload = {
                title: this.product_name,
                sku: this.product_sku,
                description: this.description,
                product_images: this.images,
                product_variants: this.product_variant,
                product_variant_prices: this.product_variant_prices
            }

            axios.put('/product/' + this.product_id, payload).then(response => {
                this.clearForm()
                console.log(response.data);
            }).catch(error => {
                this.errors = error
            })
        },
        clearForm() {
            this.product_name = ''
            this.product_sku = ''
            this.description = ''
            this.images = []
            this.product_variant = [
                {
                    option: this.variants[0].id,
                    tags: []
                }
            ]
            this.product_variant_prices = []
            this.errors = null
        },
    },
    mounted() {
        this.init()
    }
}
</script>

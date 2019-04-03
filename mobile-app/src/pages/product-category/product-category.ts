import { Component, ViewChild } from '@angular/core';
import { IonicPage, Navbar, NavController, MenuController, ModalController, NavParams, Platform, App, LoadingController, Events } from 'ionic-angular';
import { MenuProvider, ProductProvider, NotifyProvider, Online } from './../../providers/providers';
import { ProductCategoryModalPage, ProductCategoryFilterPage } from './../../pages/pages';

export interface ProductInterface {
	id: any;
  name: string;
  slug: string;
  thumb?: string;
  component?: any;
	fix_price?: string;
	sku?: string;
	returned?: string;
	flash_sale?: string;
  seller_id?: any;
  seller_detail?: any;
}

@IonicPage()
@Component({
  selector: 'page-product-category',
  templateUrl: 'product-category.html',
})
export class ProductCategoryPage 
{
  @ViewChild(Navbar) navBar: Navbar;

  bottomMenu: Array<{name: string, section: string, icon: any}> = [];
  topRightMenu: any;
  _pageParam: any;
  _paramName: string = '';
  _paramSlug: string = '';
  _paramSearch: string = undefined;
  _listData: ProductInterface[];
  _rangePrice: any;
  _fieldForm: any = {};
  _classColumn: any;
  _cdnSeller: any;
  _cdnIcon: any;
  _cdnProduct: any;
  _countProduct: number;
  _showBlank: boolean = false;
  
  _cart_notif:any;
  _bell_notif:any;

  constructor(
    private notif: NotifyProvider, 
    public app: App, 
    public online: Online, 
    public navCtrl: NavController, 
    public navParams: NavParams,
    public platform: Platform, 
    public menuCtrl: MenuController,
    public events: Events,
    public modalCtrl: ModalController,
    public loading: LoadingController,
    public menuProv: MenuProvider,
    public productProv: ProductProvider) 
  {
    let _paramRoots = this.navParams.get('paramRoots');
    this._pageParam = _paramRoots;
    this._paramName = _paramRoots.name;
    this._paramSlug = _paramRoots.slug;
    this._fieldForm = {filter:_paramRoots.slug};
    this.bottomMenu = this.menuProv.bottomBarMenu();
		this.loadCountCart();
  }

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
	}

  ionViewDidLoad()
  {
    this.getProduct('lookup', {init:'product-lookup', pack:{fieldForm:this._fieldForm, filterType:'category'}});
    this.navBar.backButtonClick = ()=>
    {
      this.navCtrl.pop().then(()=>{
        this.events.publish('tab:count');
      })
    }; 
  }

  ionViewDidEnter() 
  {
    if(this.navCtrl.canGoBack())
    {
      this.loadCountCart()
    }
    this.menuCtrl.swipeEnable(false);
  }

  ionViewWillLeave() 
  {
    this.menuCtrl.swipeEnable(true);
  }

	refresh(refresher)
	{
		setTimeout(() => {
			this.getProduct('lookup', {init:'product-lookup', pack:{fieldForm:this._fieldForm, filterType:'category'}});
      refresher.complete();
		}, 2000);
	}

  loadCountCart()
  {
    this.notif.get('bell').then(val=>{this._bell_notif = val})
    this.notif.get('cart').then(val=>{this._cart_notif = val})
    setTimeout(()=>{this.setNotif()}, 500)
  }

  setNotif()
  {
    this.topRightMenu = this.menuProv.topBarMenu({cart:this._cart_notif, bell:this._bell_notif});
  }
  
  async getProduct(endpoint, param?: any)
  {
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...'
    });
    loader.present();
    await this.productProv.filter(endpoint, param).subscribe((res: any) => {
      let theRes = res[endpoint + 'Product'];
      let theItems = theRes.items;
      this._listData = theItems.product;
      this._rangePrice = theItems.range_price;
			this._cdnSeller = theItems.server.seller;
			this._cdnIcon = theItems.server.icon;
      this._cdnProduct = theItems.server.product;
      this._countProduct = theItems.total;
      this._showBlank = false;
      loader.dismiss();
    },
		(err) => {
      this._showBlank = true;
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }

  searchTyping(e)
  {
    let val = e.target.value;
    this._paramSearch = val;
  }

  searchPress(key)
  {
    // Deteksi tombol enter
    if(key.charCode == 13)
    {
      // Hapus index search pada object fieldform jika param search kosong atau undefined
      if(this._paramSearch == undefined || this._paramSearch == '')
      {
        if("search" in this._fieldForm) delete this._fieldForm.search;
      }
      else
      {
        // tambahkan index search pada object fieldform jika param search terdapat teks
        if(this._paramSearch.length > 3)
        {
          this._fieldForm['search'] = this._paramSearch;
        }
        else
        {
          // Hapus index search pada object fieldform jika param search kurang dari 3 karakter
          if("search" in this._fieldForm) delete this._fieldForm.search;
        }
      }

      this.getProduct('lookup', {
        init:'product-lookup', 
        pack:{
          fieldForm:this._fieldForm, filterType:'category'
        }
      })
    }
  }

  goToNextTab(tabs)
  {
    if(tabs.index == undefined)
    {
      this.openPage(tabs);
    }
    else
    {
      //this.navCtrl.getPrevious().data.loadDataAppHome = false;
      this.events.publish('tab:clicked',{tab:tabs.index});
      if(this.navCtrl.canGoBack())
      {
        this.navCtrl.popToRoot().then(()=>{})
      }
    }
  }

  openPage(page)
  {
		this.navCtrl.push(page.component,
			{
				paramRoots:
				{
					slug:page.slug,
					name:page.name,
					component:page.component
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  }

	openProduct(page: ProductInterface)
	{
		this.navCtrl.push(page.component,
			{
				paramRoots:
				{
					id:page.id,
					slug:page.slug,
					name:page.name,
					component:page.component
				}
			},
			{
			animate:true,
			direction: 'enter'
			}
		);
  }

  bottomMenuFunction(param?:any)
  {
    if(param.passing.method == 'menu')
    {
      this.menuCtrl.open();
    }
    else
    {
      if(param.filtertype)
      {
        const _fieldForm = this._fieldForm;
        const passedparam = param.passing;
        // Merge/Menggabungkan beberapa object untuk param
        const paramMerge = {...param, ..._fieldForm, ...passedparam};
        // Menghapus index object passing setelah object di merge
        if("passing" in paramMerge) delete paramMerge.passing;
        // Menentukan modal terbuka atau tidak sesuai kondisi yg ditentukan
        const decisionOpen = this._showBlank == false ? true : paramMerge.section == 'category' ? true : false;
        if(decisionOpen == true)
        {
          this.openFilterModal(paramMerge);
        }
      }
    }
  }

  openFilterModal(param?:any) 
  {
    if(param)
    {
      // Menambahkan object index price_max / price_min
      if(param.section == 'filter')
      {
        if("price_min" in param != true) param['price_min'] = this._rangePrice.min;
        if("price_max" in param != true) param['price_max'] = this._rangePrice.max;
      }
      // Condition membuka template component sesuai jenis section
      let PageModalChoose = param.section == 'filter' ? ProductCategoryModalPage : ProductCategoryFilterPage;
      let addModal = this.modalCtrl.create(PageModalChoose, {modalParam:param});
      addModal.onDidDismiss(data => {
        if(data)
        {
          this._paramSlug = data.filter;
          // Merge/Menggabungkan beberapa object untuk field form
          this._fieldForm = data.mergefield == true ? {...this._fieldForm, ...data} : data;
          // Mengubah Nama Kategori untuk placeholder searchbox
          if(data.categoryname != undefined) this._paramName = data.categoryname;
          // Loading list product baru sesuai nilai filter yang dipilih ketika modalbox di tutup
          this.getProduct('lookup', {init:'product-lookup', pack:{fieldForm:this._fieldForm, filterType:param.filtertype}});
        }
      })
      addModal.present();
    }
  }
}

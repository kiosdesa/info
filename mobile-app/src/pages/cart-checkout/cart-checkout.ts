import { Component, ViewChild } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { IonicPage, Platform, NavController, NavParams, Navbar, AlertController, ToastController, LoadingController, ModalController } from 'ionic-angular';
import { RequestApiProvider, Online } from './../../providers/providers';
import { ShippingChoosePage, AddressChoosePage } from './../../pages/pages';

@IonicPage()
@Component({
  selector: 'page-cart-checkout',
  templateUrl: 'cart-checkout.html',
})
export class CartCheckoutPage 
{
  @ViewChild(Navbar) navBar: Navbar;

  _paramRoots: any;
  _dataOrders: any;
	_classColumn: any;
	_cdnSeller: any;
	_cdnIcon: any;
	_cdnProduct: any;
	_totalPrice: any;
  _totalPriceValue: any;
  _totalPriceFormat: string;
  _showBlank: boolean;
	_allButtonStatus: boolean;
	_payButtonStatus: boolean;
  _dataToken: any;
  
  errorText: string;
	CancelTitle: string;
	CancelMessage: string;
	CancelButton: string;
  ConfirmButton: string;
  
  ToAddressUsers: any;
  ChooseShipping: any = [];
  newListOrder: any;

  constructor(
    private translate: TranslateService, 
		private online: Online, 
    private reqapiprov: RequestApiProvider, 
		public platform: Platform, 
    public loading: LoadingController, 
    public toast: ToastController, 
    public alert: AlertController, 
    public navCtrl: NavController, 
    public navParams: NavParams,
    public modal:ModalController) 
  {
    this.translate.get(['ERROR', 'CANCEL_TITLE', 'CANCEL_MESSAGE', 'CANCEL_BUTTON', 'CONFIRM_BUTTON']).subscribe((value) => {
			this.errorText = value.ERROR;
      this.CancelTitle = value.CANCEL_TITLE;
      this.CancelMessage = value.CANCEL_MESSAGE;
			this.CancelButton = value.CANCEL_BUTTON;
			this.ConfirmButton = value.CONFIRM_BUTTON;
    });
    
    let _paramRoots = this.navParams.get('paramRoots');
    this._paramRoots = _paramRoots;
    this.loadOrder()
  }

  ngOnInit()
  {
    this._totalPrice = 0;
    this._totalPriceValue = 0;
    this._showBlank = false;
    this._allButtonStatus = false;
    this._payButtonStatus = false;
    this._dataToken = [];
    this.ToAddressUsers = undefined;
    this.newListOrder = undefined;
  }

	ngAfterViewInit() 
	{
		this._classColumn = this.platform.width() > 900 ? 'col col-big' : 'col';
  }
  
  ionViewDidLoad()
  {
    this.navBar.backButtonClick = ()=>
    {
      this.removeOrder()
    }; 
  }


  async loadOrder()
  {
    let cluster = 'transaction';
    let endpoint = 'getCheckout';
    let loader = this.loading.create({
      spinner: 'dots',
      content: 'Loading...',
    });
    loader.present();
    await this.reqapiprov
    .post(cluster, endpoint, {init:'checkout-get', pack:{fieldForm:{seller_detail:true}}})
    .subscribe((res: any) => {
      let theRes = res[endpoint];
      let theItems = theRes.items;
      if(theItems.approve == true)
      {
        this._allButtonStatus = false;
        this._showBlank = false;
        this._dataOrders = theItems.data;
        this._cdnSeller = theItems.server.seller;
        this._cdnProduct = theItems.server.product;
        this._cdnIcon = theItems.server.icon;
        this._totalPrice = theItems.total_price;
        this._totalPriceValue = theItems.total_price_format.value;
        this._totalPriceFormat = theItems.total_price_format.symbol;
      }
      else
      {
        this._allButtonStatus = true;
        this._showBlank = true;
      }
      loader.dismiss();
    },
    (err) => {
      this._allButtonStatus = true;
      this._showBlank = true;
      loader.dismiss();
      this.online.checkOnline(false);
    })
  }


  async address()
  {
    let addModal = this.modal.create(AddressChoosePage);
    addModal.onDidDismiss(data => {
      if(data)
      {
        this.ToAddressUsers = data;
        if(this.ChooseShipping.length > 0) this.ChooseShipping = []; this._payButtonStatus = false;
      }
    })
    addModal.present();
  }


  async shipping(param?:any)
  {
    let paramsShipping = {
      type:'tokped',
      switch:'rate',
      from:param.from,
      to:param.to,
      weight:param.weight,
      service:'regular',
      thenames:param.shippingname
    };
    let addModal = this.modal.create(ShippingChoosePage, {modalParam:paramsShipping});
    addModal.onDidDismiss(data => {
      if(data)
      {
        this.calculate(param, data)
      }
    })
    addModal.present();
  }

  calculate(param, data)
  {
    this.ChooseShipping[param.index] = data;
    // Mengatasi nilai undefined
    const source: (string|undefined)[] = this.ChooseShipping;
    const filteredShipping: string[] = source.filter(element => element !== undefined)
    // Jika total objek sama maka penghitungan dilakukan
    if(filteredShipping.length == this._dataOrders.length)
    {
      setTimeout(()=>{
        const totalTariff = this.ChooseShipping.reduce((sum, item) => sum + item.shipper_price, 0);
        const totalPrice = this._dataOrders.reduce((sum, item) => sum + item.subtotal_price, 0);
        this._payButtonStatus = true;
        this._totalPriceValue = totalPrice + totalTariff;
        let newListOrder = [];
        let _dataOrders = this._dataOrders;
        for(let i=0;i < _dataOrders.length;i++)
        {// Adalah untuk passing data ke Request API
          let _shippingName_ = this.ChooseShipping[i]['shipper_name'];
          newListOrder[i] = {
            tc:_dataOrders[i]['token_cart'],
            sc:
            {
              asc:this.ToAddressUsers.code_format,
              isc:this.ChooseShipping[i]['shipper_id'], 
              nsc:_shippingName_.replace(/([\&])/gi, "n")
            }
          }
        }
        this.newListOrder = newListOrder;
        //console.log(newListOrder);
      }, 600)
    }
  }

  removeOrder(param?:any)
	{
		let alert = this.alert.create({
			cssClass: 'no-scroll',
			title: this.CancelTitle,
			message: this.CancelMessage,
			buttons: [
			{
				text: this.ConfirmButton,
				handler: () => {
          this.navCtrl.popToRoot();
				}
			},
			{
				text: this.CancelButton,
				role: 'cancel'
			}]
		});
		alert.present();
  }
  
  choosepayment(p?:any)
  {
    if(this.newListOrder !== undefined)
    {
      let param = {total:this._totalPriceValue, data:this.newListOrder, adrd:this.ToAddressUsers}
      this.navCtrl.push(p.page,{paramRoots:param},{animate:true,direction:'enter'})
    }
  }
}

import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductUploadPage } from './product-upload';

@NgModule({
  declarations: [
    ProductUploadPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductUploadPage),
    TranslateModule
  ],
  exports: [
    ProductUploadPage
  ]
})
export class ProductUploadPageModule {}

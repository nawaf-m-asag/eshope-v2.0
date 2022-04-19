<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ trans('plugins/ecommerce::order.invoice_for_order') }} {{ get_order_code($order->id) }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/ecommerce/css/invoice.css') }}?v=1.1.1">
</head>
<body @if (BaseHelper::siteLanguageDirection() == 'rtl') dir="rtl" @endif>
<style>
    .line-items-container tr th{
        padding: 10px;
        border: solid 1px #555;
        text-align: right;
        color: black;
        font-weight: bold;
        font-size: 14px
    }
    .border  td,.border_td{
        padding: 10px !important;
        border: solid 0.5px #555;
        text-align: right
    }
    .container{
     height: 100px;
     margin-bottom: 10px;
     }
    .container >div{
       
        float: right;;
    }
    .right-container{
        width: 30%;
    line-height: 33px
    }
    .center-container{
        width: 33%;
        display: flex;
        justify-content: center
    }
    .left-container{
        width: 33%;
       
        line-height: 30px
    }
</style>

@php
    $logo = theme_option('logo_in_invoices') ?: theme_option('logo');
@endphp
<div class="container">

    <div class="left-container">
      <strong>{{ get_ecommerce_setting('store_name') }}</strong>  
        <br>
        {{ get_ecommerce_setting('store_address') }}
        <br>
        {{ get_ecommerce_setting('store_phone') }}
    </div>
    <div class="center-container">
        @if ($logo)
            <img src="{{ RvMedia::getImageUrl($logo) }}"
                 style="width:100%; max-width:100px;" alt="{{ theme_option('site_title') }}">
        @endif
    </div>
    <div class="right-container">
       {{ trans('plugins/ecommerce::order.invoice') }}: <strong>{{ get_order_code($order->id) }}</strong>
<br>
      {{ trans('plugins/ecommerce::order.created') }}: <strong>{{ now()->format('Y-m-d') }}</strong>
    </div>
</div>
<hr>
<br>
<br>
<table class="invoice-info-container border">

    <tr>
        <td  class="">
            {{ trans('plugins/ecommerce::order.customer_label') }}
        </td>
        <td>
            {{ $order->address->name }}
        </td>
    </tr>
    <tr>
        
        <td rowspan="">
            {{ trans('plugins/ecommerce::order.address') }}
         </td>
        <td>
            {{ $order->full_address }}
        </td>
    </tr>
    @if ($order->address->phone!='')
    <tr>
        <td> {{ trans('plugins/ecommerce::order.phone') }}</td>
        <td>
            {{ $order->address->phone ?? 'N/A' }}
        </td>
    </tr> 
    @endif
  
    @if (isset($order->address->email)&& $order->address->email!='')
    
    <tr>
        <td> {{ trans('plugins/ecommerce::order.email') }}</td>
        <td>
            {{ $order->address->email ?? 'N/A' }}
        </td>
    </tr>
    @endif
</table>


<table class="line-items-container">
    <thead>
    <tr>
        <th class="heading-description">{{ trans('plugins/ecommerce::products.form.product') }}</th>
        <th class="heading-description">{{ trans('plugins/ecommerce::products.form.options') }}</th>
        <th class="heading-quantity">{{ trans('plugins/ecommerce::products.form.quantity') }}</th>
        <th class="heading-price">{{ trans('plugins/ecommerce::products.form.price') }}</th>
        <th class="heading-subtotal">{{ trans('plugins/ecommerce::products.form.total') }}</th>
    </tr>
    </thead>
    <tbody>

        @foreach ($order->products as $orderProduct)
        
            @php
                $product = get_products([
                    'condition' => [
                        'ec_products.status' => \Botble\Base\Enums\BaseStatusEnum::PUBLISHED,
                        'ec_products.id' => $orderProduct->product_id,
                    ],
                    'take' => 1,
                    'select' => [
                        'ec_products.id',
                        'ec_products.images',
                        'ec_products.name',
                        'ec_products.price',
                        'ec_products.sale_price',
                        'ec_products.sale_type',
                        'ec_products.start_date',
                        'ec_products.end_date',
                        'ec_products.sku',
                    ],
                ]);
            @endphp
            @if (!empty($product))
            
                <tr class="border">
                    <td>
                        {{ $product->name }}
                    </td>
                    <td>
                        <small>{{ $product->variation_attributes }}</small>

                        @if (!empty($orderProduct->options) && is_array($orderProduct->options))
                            @foreach($orderProduct->options as $option)
                                @if (!empty($option['key']) && !empty($option['value']))
                                    <p class="mb-0">
                                        <small>{{ $option['key'] }}:
                                            <strong> {{ $option['value'] }}</strong></small>
                                    </p>
                                @endif
                            @endforeach
                        @endif
                    </td>
                    <td>
                        {{ $orderProduct->qty }}
                    </td>
                    <td class="right border_td">
                       
                        @if ($product->sale_price != $product->price &&$product->sale_price!='')
        
                            {!! htmlentities(format_price($product->sale_price)) !!}
                            <del>{!! htmlentities(format_price($product->price)) !!}</del>
                        @else
                            {!! htmlentities(format_price($product->price)) !!}
                        @endif
                    </td>
                    <td class="bold border_td">
                        @if ($product->sale_price != $product->price &&$product->sale_price!='')
                            {!! htmlentities(format_price($product->sale_price * $orderProduct->qty)) !!}
                        @else
                            {!! htmlentities(format_price($product->price * $orderProduct->qty)) !!}
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach

        <tr>
            <td colspan="2"></td>
            <td colspan="2" class="right border_td">
                {{ trans('plugins/ecommerce::products.form.sub_total') }}
            </td>
            <td class="bold border_td">
                {!! htmlentities(format_price($order->sub_total)) !!}
            </td>
        </tr>
        @if (EcommerceHelper::isTaxEnabled()&&$order->tax_amount!=0)
            <tr>
                <td colspan="2"></td>
                <td colspan="2" class="right border_td">
                    {{ trans('plugins/ecommerce::products.form.tax') }}
                </td>
                <td class="bold border_td">
                    {!! htmlentities(format_price($order->tax_amount)) !!}
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="2"></td>
            <td class="border_td" colspan="2" class="right">
                {{ trans('plugins/ecommerce::products.form.shipping_fee') }}
            </td>
            <td class="border_td" class="bold">
                {!! htmlentities(format_price($order->shipping_amount)) !!}
            </td>
        </tr>
        @if ($order->discount_amount!=0)
        <tr>
            <td colspan="2">
            </td>
            <td colspan="2" class="right border_td">
                {{ trans('plugins/ecommerce::products.form.discount') }}
            </td>
            <td class="bold border_td">
                {!! htmlentities(format_price($order->discount_amount)) !!}
            </td>
        </tr>
        @endif
     
    </tbody>
</table>
<span class="stamp @if ($order->payment->status == \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED) is-completed @else is-failed @endif">{{ $order->payment->status->label() }}</span>

<table class="line-items-container">
    <thead>
    <tr>
        <th>{{ trans('plugins/ecommerce::order.payment_info') }}</th>
        <th>{{ trans('plugins/ecommerce::order.total_amount') }}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td class="payment-info border_td">
            <div>
                {{ trans('plugins/ecommerce::order.payment_method') }}: <strong>{{ $order->payment->payment_channel->label() }}</strong>
            </div>
            <div>
                {{ trans('plugins/ecommerce::order.payment_status_label') }}: <strong>{{ $order->payment->status->label() }}</strong>
            </div>
        </td>
        <td class="large total border_td">{!! htmlentities(format_price($order->amount)) !!}</td>
    </tr>
    </tbody>
</table>
</body>
</html>
<script>
    window.print();
    </script>